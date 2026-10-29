<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<FieldType>
 */
#[IsGranted('ROLE_ADMIN')]
class FieldTypeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TitleTranslationFieldRenderer $titleTranslationFieldRenderer,
        private readonly TitleTranslationSynchronizer $titleTranslationSynchronizer,
        private readonly FieldTypeRepository $fieldTypeRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return FieldType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            FormField::addColumn(6),
            FormField::addFieldset(new TranslatableMessage('field_type.entity.title', [], 'admin')),
            TextField::new('code', new TranslatableMessage('field_type.labels.code', [], 'admin')),
            TextField::new('title', new TranslatableMessage('field_type.labels.title', [], 'admin'))
                ->formatValue(
                    fn (?string $value, FieldType $fieldType): string => $fieldType->getTranslatedTitleByLocale($this->translator->getLocale()),
                ),
            BooleanField::new('translatable', new TranslatableMessage('field_type.labels.translatable', [], 'admin')),
            BooleanField::new('filterable', new TranslatableMessage('field_type.labels.filterable', [], 'admin')),
            ChoiceField::new('inputType', new TranslatableMessage('field_type.labels.input_type', [], 'admin'))
                ->setFormTypeOption('attr', [
                    'data-controller' => 'field-type-input-type',
                    'data-action' => 'change->field-type-input-type#update',
                    'data-field-type-input-type-number-value' => InputType::Number->value,
                ]),
            TextField::new('decimalSeparator', new TranslatableMessage('field_type.labels.decimal_separator', [], 'admin'))
                ->setEmptyData()
                ->setMaxLength(1)
                ->onlyOnForms(),
            FormField::addColumn(6),
        ];

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $fieldType = $this->getContext()
                ?->getEntity()
                ->getInstance();

            $fields = array_merge(
                $fields,
                $this->titleTranslationFieldRenderer->render($fieldType, new TranslatableMessage('field_type.labels.title', [], 'admin'))
            );
        }

        return $fields;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('field_type.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('field_type.entity.title_plural', [], 'admin'))
            ->setSearchFields([
                'title',
                'code',
                'titleTranslations.value',
            ])
            ->autocomplete(
                callback: fn (FieldType $fieldType): string => $fieldType->getTranslatedTitleByLocale($this->translator->getLocale()).' ('.$fieldType->getCode().' - '.$fieldType->getInputType()->trans($this->translator).')',
            )
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (FieldType $fieldType) => !$this->fieldTypeRepository->isInUse($fieldType))
            )
            ->update(Crud::PAGE_DETAIL, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (FieldType $fieldType) => !$this->fieldTypeRepository->isInUse($fieldType))
            )
            ->disable(Action::BATCH_DELETE);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('FieldType');

        $this->titleTranslationSynchronizer->synchronizeTitleTranslations($entityInstance, $requestData);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createEntity(string $entityFqcn): ?object
    {
        $entity = parent::createEntity($entityFqcn);

        if (!$entity instanceof FieldType) {
            return $entity;
        }

        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('FieldType');

        $this->titleTranslationSynchronizer->synchronizeTitleTranslations($entity, $requestData);

        return $entity;
    }
}
