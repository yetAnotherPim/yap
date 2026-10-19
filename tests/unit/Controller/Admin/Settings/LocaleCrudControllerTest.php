<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\LocaleCrudController;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(LocaleCrudController::class)]
class LocaleCrudControllerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepository;
    private LocaleCrudController $controller;

    public function setUp(): void
    {
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->controller = new LocaleCrudController($this->localeRepository);

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Locale::class, LocaleCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(3, $fields);

        $this->assertInstanceOf(LocaleField::class, $fields[0]);
        $this->assertEquals('id', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[1]);
        $this->assertEquals('default', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[2]);
        $this->assertEquals('contentTranslationEnabled', $fields[2]->getAsDto()->getProperty());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSetLocaleAsDefault(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')
            ->once()
            ->andReturn('de');

        $this->localeRepository
            ->shouldReceive('setDefaultLocale')
            ->once()
            ->with('de');

        $adminUrlGenerator = \Mockery::mock(\stdClass::class);
        $adminUrlGenerator
            ->shouldReceive('setAction')
            ->once()
            ->with(Action::INDEX)
            ->andReturnSelf();

        $adminUrlGenerator
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('/admin?crudAction=index');

        $container = \Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGenerator);

        $this->controller->setContainer($container);

        $response = $this->controller->setLocaleAsDefault($locale);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin?crudAction=index', $response->getTargetUrl());
    }

    public function testConfigureActions(): void
    {
        $actions = Actions::new()
            ->add(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DELETE);

        $configured = $this->controller->configureActions($actions);
        $indexActions = $configured->getAsDto(Crud::PAGE_INDEX)->getActions();

        $this->assertArrayNotHasKey(Action::EDIT, $indexActions);
        $this->assertArrayHasKey(Action::DELETE, $indexActions);
        $this->assertArrayHasKey('asDefault', $indexActions);

        /** @phpstan-var ActionDto $asDefault */
        $asDefault = $indexActions['asDefault'];
        $this->assertSame('asDefault', $asDefault->getName());
        $this->assertSame('Set as Default', $asDefault->getLabel());
        $this->assertSame('fa fa-check', $asDefault->getIcon());
        $this->assertSame('setLocaleAsDefault', $asDefault->getCrudActionName());
    }

    public function testConfigureCrud(): void
    {
        $controller = new LocaleCrudController(\Mockery::mock(LocaleRepository::class));

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'locale.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'locale.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );
    }
}
