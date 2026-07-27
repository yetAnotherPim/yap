<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Products;

use App\Controller\Admin\Products\ProductCrudController;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\ButtonVariant;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProductCrudController::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(Product::class)]
#[UsesClass(TimestampableEntity::class)]
class ProductCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Product::class, ProductCrudController::getEntityFqcn());
    }

    public function testCreateEntityCreatesProduct(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $repository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $controller = new ProductCrudController($repository);

        $product = $controller->createEntity(Product::class);

        $this->assertInstanceOf(Product::class, $product);
    }

    public function testConfigureActions(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $controller = new ProductCrudController($repository);
        $actions = Actions::new()
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN);

        $result = $controller->configureActions($actions);

        $this->assertCount(3, $result->getAsDto(Crud::PAGE_NEW)->getActions());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)?->getVariant());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)?->getVariant());
        $this->assertEquals(ButtonVariant::Primary, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)?->getVariant());
    }

    public function testCreateEntityInitializesIdentifiers(): void
    {
        $type1 = \Mockery::mock(IdentifierType::class);
        $type1->shouldReceive('getId')
            ->andReturn(Uuid::v7());

        $type2 = \Mockery::mock(IdentifierType::class);
        $type2->shouldReceive('getId')
            ->andReturn(Uuid::v7());

        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $repository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$type1, $type2]);

        $controller = new ProductCrudController($repository);

        $product = $controller->createEntity(Product::class);

        $this->assertCount(2, $product->getIdentifiers());
    }

    public function testCreateEntityDoesNotCreateIdentifiersWhenNoIdentifierTypesExist(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $repository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $controller = new ProductCrudController($repository);

        $product = $controller->createEntity(Product::class);

        $this->assertCount(
            0,
            $product->getIdentifiers()
        );
    }

    public function testUpdateEntityRemovesEmptyIdentifiers(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $emptyIdentifier = \Mockery::mock(Identifier::class);
        $emptyIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('');

        $validIdentifier = \Mockery::mock(Identifier::class);
        $validIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('ABC123');

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->once()
            ->andReturn(new ArrayCollection([$emptyIdentifier, $validIdentifier]));

        $product
            ->shouldReceive('removeIdentifier')
            ->once()
            ->with($emptyIdentifier);

        $product
            ->shouldReceive('removeIdentifier')
            ->never()
            ->with($validIdentifier);

        $product
            ->shouldReceive('updateUpdatedAt')
            ->once();

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $controller = new ProductCrudController($repository);
        $controller->updateEntity($entityManager, $product);
    }

    public function testPersistEntityRemovesEmptyIdentifiers(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $emptyIdentifier = \Mockery::mock(Identifier::class);
        $emptyIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('');

        $validIdentifier = \Mockery::mock(Identifier::class);
        $validIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('EAN123');

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->once()
            ->andReturn(new ArrayCollection([$emptyIdentifier, $validIdentifier]));

        $product
            ->shouldReceive('removeIdentifier')
            ->once()
            ->with($emptyIdentifier);

        $product
            ->shouldReceive('removeIdentifier')
            ->never()
            ->with($validIdentifier);

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $controller = new ProductCrudController($repository);
        $controller->persistEntity($entityManager, $product);
    }

    public function testConfigureFieldsForIndexPageReturnsExpectedFields(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $controller = new ProductCrudController($repository);

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(4, $fields);

        $this->assertInstanceOf(CollectionField::class, $fields[0]);
        $this->assertEquals('identifiers', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(AssociationField::class, $fields[1]);
        $this->assertEquals('productType', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(DateTimeField::class, $fields[2]);
        $this->assertEquals('createdAt', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(DateTimeField::class, $fields[3]);
        $this->assertEquals('updatedAt', $fields[3]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsForFormPageReturnsExpectedFields(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $controller = new ProductCrudController($repository);

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_NEW));

        $this->assertCount(5, $fields);

        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Field\FormField::class, $fields[0]);
        $this->assertInstanceOf(AssociationField::class, $fields[1]);
        $this->assertEquals('productType', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(CollectionField::class, $fields[2]);
        $this->assertEquals('identifiers', $fields[2]->getAsDto()->getProperty());
        $this->assertEquals(false, $fields[2]->getAsDto()->getFormTypeOption('allowAdd'));
        $this->assertEquals(false, $fields[2]->getAsDto()->getFormTypeOption('allowDelete'));
        $this->assertInstanceOf(DateTimeField::class, $fields[3]);
        $this->assertEquals('createdAt', $fields[3]->getAsDto()->getProperty());
        $this->assertEquals('disabled', $fields[3]->getAsDto()->getFormTypeOption('disabled'));
        $this->assertInstanceOf(DateTimeField::class, $fields[4]);
        $this->assertEquals('updatedAt', $fields[4]->getAsDto()->getProperty());
        $this->assertEquals('disabled', $fields[4]->getAsDto()->getFormTypeOption('disabled'));
    }

    public function testCreateEditFormInitializesMissingIdentifiers(): void
    {
        $existingTypeId = Uuid::v7();
        $missingTypeId = Uuid::v7();

        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $identifierType1 = \Mockery::mock(IdentifierType::class);
        $identifierType1->shouldReceive('getId')->andReturn($existingTypeId);

        $identifierType2 = \Mockery::mock(IdentifierType::class);
        $identifierType2->shouldReceive('getId')->andReturn($missingTypeId);

        $repository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$identifierType1, $identifierType2]);

        $existingIdentifierType = \Mockery::mock(IdentifierType::class);
        $existingIdentifierType
            ->shouldReceive('getId')
            ->andReturn($existingTypeId);

        $identifier = \Mockery::mock(Identifier::class);
        $identifier
            ->shouldReceive('getType')
            ->andReturn($existingIdentifierType);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->times(2)
            ->andReturn(new ArrayCollection([$identifier]));

        $product
            ->shouldReceive('addIdentifier')
            ->once()
            ->with(\Mockery::on(
                static fn (Identifier $identifier): bool => $identifier->getType() === $identifierType2
            ));

        /** @var EntityDto<Product> $entityDto */
        $entityDto = new EntityDto(
            Product::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            $product
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$repository]
        )->makePartial();

        /** @phpstan-var AdminContext<Product> $context */
        $context = AdminContext::forTesting();

        $this->expectException(\Error::class);
        $this->expectExceptionMessageIsOrContains(
            'AbstractController::$container'
        );

        $controller->createEditForm(
            $entityDto,
            KeyValueStore::new(),
            $context
        );
    }

    public function testCreateEditFormSkipsInitializationForNonProductInstance(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $repository->shouldNotReceive('findAll');

        /** @var EntityDto<Product> $entityDto */
        $entityDto = new EntityDto(
            \stdClass::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            new \stdClass()
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$repository]
        )->makePartial();

        $this->expectException(\Error::class);
        $this->expectExceptionMessageIsOrContains(
            'AbstractController::$container'
        );

        /** @phpstan-var AdminContext<Product> $context */
        $context = AdminContext::forTesting();

        $controller->createEditForm(
            $entityDto,
            KeyValueStore::new(),
            $context
        );
    }

    public function testConfigureCrudHidesNullValues(): void
    {
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $controller = new ProductCrudController($repository);

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertTrue(
            $configuredCrud->getAsDto()->areNullValuesHidden()
        );
    }
}
