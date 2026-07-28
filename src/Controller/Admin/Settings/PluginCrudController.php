<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Event\Enum\Action as PluginAction;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\PluginManager;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Plugin>
 */
class PluginCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PluginManager $pluginManager,
        private readonly CacheClearer $cacheClearer,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Plugin::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX,
                Action::new('refresh', 'Reload', 'fa fa-arrows-rotate')
                    ->createAsGlobalAction()
                    ->asPrimaryAction()
                    ->linkToCrudAction('refreshPlugins')
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('activate', 'Activate', 'fa fa-check')
                    ->linkToCrudAction('activatePlugin')
                    ->displayIf(fn (Plugin $plugin) => !$plugin->isActive())
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('deactivate', 'Deactivate', 'fa fa-ban')
                    ->linkToCrudAction('deactivatePlugin')
                    ->displayIf(fn (Plugin $plugin) => $plugin->isActive())
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('update', 'Update', 'fa fa-wrench')
                    ->linkToCrudAction('updatePlugin')
                    ->displayIf(fn (Plugin $plugin) => $plugin->isUpdatable())
            );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws PluginWorkflowException
     */
    #[AdminRoute('/refreshPlugins')]
    public function refreshPlugins(): Response
    {
        $this->cacheClearer->clear();
        $this->pluginManager->getPlugins();

        return $this->redirectToIndex();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[AdminRoute('/{id}/activate')]
    public function activatePlugin(Plugin $plugin): Response
    {
        try {
            $this->pluginManager->performWorkflow(PluginAction::ACTIVATE, $plugin->getName());
        } catch (PluginWorkflowException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[AdminRoute('/{id}/deactivate')]
    public function deactivatePlugin(Plugin $plugin): Response
    {
        try {
            $this->pluginManager->performWorkflow(PluginAction::DEACTIVATE, $plugin->getName());
        } catch (PluginWorkflowException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws PluginWorkflowException
     */
    #[AdminRoute('/{id}/update')]
    public function updatePlugin(Plugin $plugin): Response
    {
        try {
            $this->pluginManager->performWorkflow(PluginAction::UPDATE, $plugin->getName());
        } catch (PluginWorkflowException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToIndex();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('namespace'),
            TextField::new('version'),
            TextField::new('installedVersion'),
            BooleanField::new('active'),
        ];
    }

    private function redirectToIndex(): Response
    {
        /** @phpstan-ignore-next-line */
        $url = $this->container->get(AdminUrlGeneratorInterface::class)->setAction(Action::INDEX)->generateUrl();

        /** @phpstan-var string $url */
        return $this->redirect($url);
    }
}
