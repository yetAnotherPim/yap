<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Plugin\Plugin;
use App\PluginManager\AssetMapper\AssetMapCompiler;
use App\PluginManager\AssetMapper\Exception\AssetCompilationException;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<Plugin>
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class PluginCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PluginManager $pluginManager,
        private readonly CacheClearer $cacheClearer,
        private readonly AssetMapCompiler $assetMapCompiler,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Plugin::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, new TranslatableMessage('plugin_manager.title', [], 'admin'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX,
                Action::new('refresh', new TranslatableMessage('plugin_manager.labels.actions.reload', [], 'admin'), 'fa fa-arrows-rotate')
                    ->createAsGlobalAction()
                    ->asPrimaryAction()
                    ->linkToCrudAction('refreshPlugins')
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('compileAssets', new TranslatableMessage('plugin_manager.labels.actions.compile', [], 'admin'), 'fa fa-gears')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('compileAssets')
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('activate', new TranslatableMessage('plugin_manager.labels.actions.activate', [], 'admin'), 'fa fa-check')
                    ->linkToCrudAction('activatePlugin')
                    ->displayIf(fn (Plugin $plugin) => !$plugin->isActive())
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('deactivate', new TranslatableMessage('plugin_manager.labels.actions.deactivate', [], 'admin'), 'fa fa-ban')
                    ->linkToCrudAction('deactivatePlugin')
                    ->displayIf(fn (Plugin $plugin) => $plugin->isActive())
            )
            ->add(Crud::PAGE_INDEX,
                Action::new('update', new TranslatableMessage('plugin_manager.labels.actions.update', [], 'admin'), 'fa fa-wrench')
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

    #[AdminRoute('/compileAssets')]
    public function compileAssets(): Response
    {
        try {
            $this->assetMapCompiler->compile();
        } catch (AssetCompilationException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToIndex();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', new TranslatableMessage('plugin_manager.labels.name', [], 'admin')),
            TextField::new('namespace', new TranslatableMessage('plugin_manager.labels.namespace', [], 'admin')),
            TextField::new('version', new TranslatableMessage('plugin_manager.labels.version', [], 'admin')),
            TextField::new('installedVersion', new TranslatableMessage('plugin_manager.labels.installed_version', [], 'admin')),
            BooleanField::new('active', new TranslatableMessage('plugin_manager.labels.active', [], 'admin')),
            TextField::new('assetCompilationStatus', new TranslatableMessage('plugin_manager.labels.asset_compilation_required', [], 'admin'))
                ->setValue('')
                ->formatValue(
                    static fn (mixed $value, Plugin $plugin): string => $plugin->isAssetCompilationRequired()
                        ? '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>'
                        : '<span aria-hidden="true"></span>',
                )
                ->renderAsHtml(),
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
