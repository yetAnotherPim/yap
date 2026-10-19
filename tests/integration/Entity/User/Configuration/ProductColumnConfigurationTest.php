<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\User\Configuration;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ProductColumnConfiguration::class)]
#[UsesClass(User::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
class ProductColumnConfigurationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $this->entityManager = $entityManager;

        parent::setUp();
    }

    public function testDeleteUserRemovesProductColumnConfigurations(): void
    {
        $user = UserFactory::createOne();
        $fieldType = FieldTypeFactory::createOne();

        $configuration = new ProductColumnConfiguration();
        $configuration
            ->setUser($user)
            ->setFieldType($fieldType)
            ->setPosition(0);

        $this->entityManager->persist($configuration);
        $this->entityManager->flush();

        $repository = $this->entityManager->getRepository(
            ProductColumnConfiguration::class,
        );

        $this->assertCount(1, $repository->findAll());

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->assertCount(0, $repository->findAll());
    }
}
