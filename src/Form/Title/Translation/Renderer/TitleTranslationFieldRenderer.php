<?php

declare(strict_types=1);

namespace App\Form\Title\Translation\Renderer;

use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Resolver\TitleTranslationResolver;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class TitleTranslationFieldRenderer
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly TitleTranslationResolver $titleTranslationResolver,
    ) {
    }

    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param TranslatableTitleInterface<TTitleTranslation>|null $entity
     *
     * @return array<FieldInterface>
     */
    public function render(?TranslatableTitleInterface $entity, string|TranslatableInterface $label): array
    {
        $fields = [];
        $locales = $this->localeRepository->getAvailableInterfaceTranslationLocales();

        if (true === $locales->isEmpty()) {
            return $fields;
        }

        $localeIds = $this->localeRepository->extractLocaleIds($locales);

        $fields[] = FormField::addFieldset(new TranslatableMessage('general.labels.translation', [], 'admin'));
        $fields[] = LocaleField::new('translation_locale', new TranslatableMessage('general.labels.locale', [], 'admin'))
            ->includeOnly($localeIds)
            ->setFormTypeOptions([
                'data' => '',
                'mapped' => false,
            ])
            ->setHtmlAttribute(
                'data-controller',
                'title-translation',
            )
            ->setHtmlAttribute(
                'data-action',
                'change->title-translation#update',
            )
            ->onlyOnForms();

        foreach ($locales as $locale) {
            $fields[] = TextField::new('title-'.$locale->getId(), $label)
                ->setFormTypeOptions([
                    'data' => $this->titleTranslationResolver->resolve($entity, $locale)?->getValue(),
                    'mapped' => false,
                ])
                ->setFormTypeOption('row_attr', [
                    'data-title-translation-locale' => $locale->getId(),
                ])
                ->onlyOnForms();
        }

        return $fields;
    }
}
