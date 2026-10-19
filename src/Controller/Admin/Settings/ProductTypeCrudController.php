<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Form\ProductType\DefaultTranslation\Synchronizer\DefaultTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Repository\LocaleRepository;
use App\Repository\ProductTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<ProductType>
 */
#[IsGranted('ROLE_ADMIN')]
class ProductTypeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TitleTranslationFieldRenderer $titleTranslationFieldRenderer,
        private readonly TitleTranslationSynchronizer $titleTranslationSynchronizer,
        private readonly DefaultTranslationSynchronizer $defaultTranslationSynchronizer,
        private readonly LocaleRepository $localeRepository,
        private readonly ProductTypeRepository $productTypeRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ProductType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('product_type.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('product_type.entity.title_plural', [], 'admin'))
            ->setSearchFields([
                'title',
                'code',
                'titleTranslations.value',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('code', new TranslatableMessage('product_type.labels.code', [], 'admin')),
                TextField::new('title', new TranslatableMessage('product_type.labels.title', [], 'admin'))
                    ->formatValue(
                        fn (?string $value, ProductType $productType): string => $productType->getTranslatedTitleByLocale($this->translator->getLocale()),
                    ),
            ];
        }

        $fields = [
            FormField::addColumn(6),
            FormField::addFieldset(new TranslatableMessage('product_type.entity.title', [], 'admin')),
            TextField::new('code', new TranslatableMessage('product_type.labels.code', [], 'admin')),
            TextField::new('title', new TranslatableMessage('product_type.labels.title', [], 'admin'))
                ->formatValue(
                    fn (?string $value, ProductType $productType): string => $productType->getTranslatedTitleByLocale($this->translator->getLocale()),
                ),
            FormField::addColumn(6),
        ];

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $productType = $this->getContext()
                ?->getEntity()
                ->getInstance();

            $fields = array_merge(
                $fields,
                $this->titleTranslationFieldRenderer->render($productType, new TranslatableMessage('field_type.labels.title', [], 'admin'))
            );
        }

        $fields[] = FormField::addColumn(12);
        $fields[] = FormField::addFieldset(new TranslatableMessage('product_type.labels.fields', [], 'admin'));
        $fields[] = CollectionField::new('productTypeFieldConfigurations')
            ->setRequired(true)
            ->setEntryIsComplex()
            ->setLabel(false)
            ->addCssClass('sortable-collection')
            ->setEntryToStringMethod(
                fn (ProductTypeFieldConfiguration $productTypeFieldConfiguration): string => (null !== $productTypeFieldConfiguration->getFieldType())
                    ? $productTypeFieldConfiguration->getFieldType()->getTranslatedTitleByLocale(
                        $this->translator->getLocale(),
                    ).' ('.$productTypeFieldConfiguration->getFieldType()->getCode().' - '.$productTypeFieldConfiguration->getFieldType()->getInputType()->trans($this->translator).')'
                    : ''
            );

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (ProductType $productType) => !$this->productTypeRepository->isInUse($productType))
            )
            ->update(Crud::PAGE_DETAIL, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (ProductType $productType) => !$this->productTypeRepository->isInUse($productType))
            )
            ->disable(Action::BATCH_DELETE);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('ProductType');

        $this->titleTranslationSynchronizer->synchronizeTitleTranslations($entityInstance, $requestData);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createEntity(string $entityFqcn): object
    {
        $entity = parent::createEntity($entityFqcn);

        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('ProductType');

        $this->titleTranslationSynchronizer->synchronizeTitleTranslations($entity, $requestData);

        return $entity;
    }

    /**
     * @return FormBuilderInterface<ProductType>
     */
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $formBuilder = parent::createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $this->addDefaultTranslationListener($formBuilder);

        return $formBuilder;
    }

    /**
     * @param FormBuilderInterface<ProductType> $formBuilder
     */
    protected function addDefaultTranslationListener(
        FormBuilderInterface $formBuilder,
    ): void {
        $formBuilder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                $form = $event->getForm();

                if (!$form->has('productTypeFieldConfigurations')) {
                    return;
                }

                foreach ($form->get('productTypeFieldConfigurations') as $configurationForm) {
                    $configuration = $configurationForm->getData();

                    if (!$configuration instanceof ProductTypeFieldConfiguration) {
                        continue;
                    }

                    if (!$configurationForm->has('productTypeFieldConfigurationDefaultTranslations')) {
                        continue;
                    }

                    $translationsForm = $configurationForm->get(
                        'productTypeFieldConfigurationDefaultTranslations',
                    );

                    $values = [];

                    foreach (
                        $translationsForm as $localeId => $translationForm
                    ) {
                        $value = $translationForm->getData();

                        if (null !== $value && !\is_string($value)) {
                            continue;
                        }

                        $values[$localeId] = $value;
                    }

                    $this->defaultTranslationSynchronizer->synchronize(
                        $configuration,
                        $this->localeRepository->getAvailableContentTranslationLocales(),
                        $values,
                    );
                }
            },
        );
    }
}
