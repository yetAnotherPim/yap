<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Controller\Admin;

use App\plugins\Feeds\Entity\Feed;
use App\plugins\Feeds\Form\Field\FieldTypes\FieldTypesField;
use App\plugins\Feeds\Validator\Constraint\TwigSyntax;
use App\Repository\FieldTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<Feed>
 */
class FeedCrudController extends AbstractCrudController
{
    public function __construct(private readonly FieldTypeRepository $fieldTypeRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            FormField::addColumn(6),
            FormField::addFieldset(),
            TextField::new('name', new TranslatableMessage('labels.feed_name', [], 'feeds')),
            BooleanField::new('active', new TranslatableMessage('labels.feed_active', [], 'feeds')),
            ChoiceField::new('schedule', new TranslatableMessage('labels.feed_schedule', [], 'feeds')),
            TextField::new('filename', new TranslatableMessage('labels.feed_filename', [], 'feeds')),
            TextareaField::new('head', new TranslatableMessage('labels.feed_head', [], 'feeds'))
                ->hideOnIndex()
                ->setFormTypeOption('constraints', [
                    new TwigSyntax(),
                ]
                ),
            TextareaField::new('content', new TranslatableMessage('labels.feed_content', [], 'feeds'))
                ->hideOnIndex()
                ->setNumOfRows(50)
                ->setFormTypeOption('constraints', [
                        new TwigSyntax(),
                    ]
                ),
            TextareaField::new('footer', new TranslatableMessage('labels.feed_footer', [], 'feeds'))
                ->hideOnIndex()
                ->setFormTypeOption('constraints', [
                        new TwigSyntax(),
                    ]
                ),
            DateTimeField::new('latest', new TranslatableMessage('labels.feed_latest', [], 'feeds'))
                ->hideWhenCreating()
                ->setDisabled(true),
        ];

        // TODO: filter
        // TODO: add translations
        // TODO: prepare generation of values in field types list with or without translation
        // TODO: clean up plugin structure and reorganize folders

        if ('new' === $pageName || 'edit' === $pageName) {
            $fields[] = FormField::addColumn(6);
            $fields[] = FormField::addFieldset();

            $fields[] = FieldTypesField::new(
                'fieldTypes',
                new TranslatableMessage('field_type.entity.title_plural', [], 'admin'),
            )->setFieldTypes(
                $this->fieldTypeRepository->findBy([], ['code' => \SortDirection::Ascending])
            );
        }

        return $fields;
    }
}
