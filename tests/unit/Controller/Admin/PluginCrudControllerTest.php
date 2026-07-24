<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\PluginCrudController;
use App\Entity\Plugin\Plugin;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\PluginManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\ButtonVariant;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

#[CoversClass(PluginCrudController::class)]
#[UsesClass(PluginWorkflowException::class)]
class PluginCrudControllerTest extends MockeryTestCase
{
    private PluginCrudController $controller;
    /** @phpstan-var PluginManager|MockInterface  */
    private PluginManager $pluginManagerMock;

    /** @phpstan-var CacheClearer|MockInterface  */
    private CacheClearer $cacheClearerMock;

    public function setUp(): void
    {
        $this->pluginManagerMock = \Mockery::mock(PluginManager::class);
        $this->cacheClearerMock = \Mockery::mock(CacheClearer::class);

        $this->controller = new PluginCrudController($this->pluginManagerMock, $this->cacheClearerMock);

        parent::setUp();
    }

    public function testConfigureActions(): void
    {
        $actions = Actions::new()
            ->add(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::EDIT);

        $result = $this->controller->configureActions($actions);

        $this->assertCount(7, $result->getAsDto(Crud::PAGE_INDEX)->getActions());
        $this->assertEquals(['delete', 'new', 'edit', 'batchDelete'], $result->getAsDto(Crud::PAGE_INDEX)->getDisabledActions());
        $this->assertEquals(ButtonVariant::Primary, $result->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, 'refresh')?->getVariant());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, 'activate')?->getVariant());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, 'deactivate')?->getVariant());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, 'update')?->getVariant());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));
        $this->assertCount(5, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('name', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('namespace', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertEquals('version', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[3]);
        $this->assertEquals('installedVersion', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[4]);
        $this->assertEquals('active', $fields[4]->getAsDto()->getProperty());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testRefreshPlugins(): void
    {
        $this->pluginManagerMock
            ->shouldReceive('getPlugins')
            ->once();

        $this->cacheClearerMock
            ->shouldReceive('clear')
            ->once();

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->refreshPlugins();
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testUpdatePlugin(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::UPDATE, 'plugin');

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->updatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testUpdatePluginCausesWorkflowException(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::UPDATE, 'plugin')
            ->andThrow(new PluginWorkflowException(\App\PluginManager\Event\Enum\Action::DEACTIVATE, 'plugin', 'message'));

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);
        $requestStackMock = \Mockery::mock(RequestStack::class);
        $sessionMock = \Mockery::mock(Session::class);
        $flashBagMock = \Mockery::mock(FlashBagInterface::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with('request_stack')
            ->andReturn($requestStackMock);

        $requestStackMock
            ->shouldReceive('getSession')
            ->once()
            ->andReturn($sessionMock);

        $sessionMock
            ->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBagMock);

        $flashBagMock
            ->shouldReceive('add')
            ->once()
            ->with('error', 'message');

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->updatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testActivatePlugin(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::ACTIVATE, 'plugin');

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->activatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testActivatePluginCausesWorkflowException(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::ACTIVATE, 'plugin')
            ->andThrow(new PluginWorkflowException(\App\PluginManager\Event\Enum\Action::DEACTIVATE, 'plugin', 'message'));

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);
        $requestStackMock = \Mockery::mock(RequestStack::class);
        $sessionMock = \Mockery::mock(Session::class);
        $flashBagMock = \Mockery::mock(FlashBagInterface::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with('request_stack')
            ->andReturn($requestStackMock);

        $requestStackMock
            ->shouldReceive('getSession')
            ->once()
            ->andReturn($sessionMock);

        $sessionMock
            ->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBagMock);

        $flashBagMock
            ->shouldReceive('add')
            ->once()
            ->with('error', 'message');

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->activatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testDeactivatePlugin(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::DEACTIVATE, 'plugin');

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->deactivatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PluginWorkflowException
     */
    public function testDeactivatePluginCausesWorkflowException(): void
    {
        $pluginMock = \Mockery::mock(Plugin::class);

        $pluginMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('plugin');

        $this->pluginManagerMock
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(\App\PluginManager\Event\Enum\Action::DEACTIVATE, 'plugin')
            ->andThrow(new PluginWorkflowException(\App\PluginManager\Event\Enum\Action::DEACTIVATE, 'plugin', 'message'));

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $adminUrlGeneratorMock = \Mockery::mock(\stdClass::class);
        $requestStackMock = \Mockery::mock(RequestStack::class);
        $sessionMock = \Mockery::mock(Session::class);
        $flashBagMock = \Mockery::mock(FlashBagInterface::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with('request_stack')
            ->andReturn($requestStackMock);

        $requestStackMock
            ->shouldReceive('getSession')
            ->once()
            ->andReturn($sessionMock);

        $sessionMock
            ->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBagMock);

        $flashBagMock
            ->shouldReceive('add')
            ->once()
            ->with('error', 'message');

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('setAction')
            ->once()
            ->with('index')
            ->andReturn($adminUrlGeneratorMock);

        $adminUrlGeneratorMock
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('url');

        $response = $this->controller->deactivatePlugin($pluginMock);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Plugin::class, PluginCrudController::getEntityFqcn());
    }
}
