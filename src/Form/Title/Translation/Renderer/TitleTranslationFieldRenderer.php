<?php

declare(strict_types=1);

namespace App\Form\Title\Translation\Renderer;

use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        $locales = $this->localeRepository->getAvailableLocaleIds();
        $titleTranslations = $this->getIndexedCollection($entity);

        if (true === empty($locales)) {
            return $fields;
        }

        $fields[] = FormField::addFieldset(new TranslatableMessage('general.labels.translation', [], 'admin'));
        $fields[] = LocaleField::new('translation_locale', new TranslatableMessage('general.labels.locale', [], 'admin'))
            ->includeOnly($locales)
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
            $fields[] = TextField::new('title-'.$locale, $label)
                ->setFormTypeOptions([
                    'data' => $this->getTranslatedValue($titleTranslations, $locale),
                    'mapped' => false,
                ])
                ->setFormTypeOption('row_attr', [
                    'data-title-translation-locale' => $locale,
                ])
                ->onlyOnForms();
        }

        return $fields;
    }

    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param TranslatableTitleInterface<TTitleTranslation>|null $entity
     *
     * @return Collection<string, TTitleTranslation>
     */
    private function getIndexedCollection(?TranslatableTitleInterface $entity): Collection
    {
        if (null === $entity) {
            return new ArrayCollection();
        }

        return $entity
            ->getTitleTranslations()
            ->reduce(
                static function (
                    ArrayCollection $translations,
                    TitleTranslationInterface $translation,
                ): ArrayCollection {
                    $translations->set(
                        $translation->getLocale()->getId(),
                        $translation,
                    );

                    return $translations;
                },
                new ArrayCollection(),
            );
    }

    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param Collection<string, TTitleTranslation> $titleTranslations
     */
    private function getTranslatedValue(Collection $titleTranslations, string $locale): ?string
    {
        return $titleTranslations->get($locale)?->getValue();
    }
}
