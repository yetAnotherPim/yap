<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Controller\Admin;

use App\plugins\Feeds\Entity\Feed;
use App\plugins\Feeds\Validator\Constraint\TwigSyntax;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<Feed>
 */
class FeedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name', new TranslatableMessage('labels.feed_name', [], 'feeds')),
            BooleanField::new('active', new TranslatableMessage('labels.feed_active', [], 'feeds')),
            ChoiceField::new('schedule', new TranslatableMessage('labels.feed_schedule', [], 'feeds')),
            TextField::new('filename', new TranslatableMessage('labels.feed_filename', [], 'feeds')),
        ];

        if ('new' === $pageName || 'edit' === $pageName) {
            // TODO: filter
            // TODO: add translations
            $fields[] = TextareaField::new('head', new TranslatableMessage('labels.feed_head', [], 'feeds'))
                ->setFormTypeOption('constraints', [
                    new TwigSyntax(),
                ]);
            $fields[] = TextareaField::new('content', new TranslatableMessage('labels.feed_content', [], 'feeds'))
                ->setNumOfRows(50)
                ->setFormTypeOption('constraints', [
                    new TwigSyntax(),
                ]);
            $fields[] = TextareaField::new('footer', new TranslatableMessage('labels.feed_footer', [], 'feeds'))
                ->setFormTypeOption('constraints', [
                    new TwigSyntax(),
                ]);
        }

        $fields[] = DateTimeField::new('latest', new TranslatableMessage('labels.feed_latest', [], 'feeds'))
            ->hideWhenCreating()
            ->setDisabled(true);

        return $fields;
    }
}
