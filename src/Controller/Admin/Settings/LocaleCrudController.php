<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<Locale>
 */
#[IsGranted('ROLE_ADMIN')]
class LocaleCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Locale::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX,
                Action::new('asDefault', 'Set as Default', 'fa fa-check')
                    ->linkToCrudAction('setLocaleAsDefault')
                    ->displayIf(fn (Locale $locale) => !$locale->isDefault())
            )
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                static fn (Action $action) => $action->displayIf(fn (Locale $locale) => !$locale->isDefault())
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('locale.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('locale.entity.title_plural', [], 'admin'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            LocaleField::new('id', new TranslatableMessage('locale.entity.title', [], 'admin'))
                ->showCode(),
            BooleanField::new('default', new TranslatableMessage('locale.labels.default', [], 'admin'))
                ->onlyOnIndex()
                ->setDisabled(),
        ];
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[AdminRoute('/{id}/setLocaleAsDefault')]
    public function setLocaleAsDefault(Locale $locale): Response
    {
        $this->localeRepository->setDefaultLocale($locale->getId());

        return $this->redirectToIndex();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function redirectToIndex(): Response
    {
        /** @phpstan-ignore-next-line */
        $url = $this->container->get(AdminUrlGeneratorInterface::class)->setAction(Action::INDEX)->generateUrl();

        /** @phpstan-var string $url */
        return $this->redirect($url);
    }
}
