<?php

declare(strict_types=1);

namespace App\Entity\Translation\Title;

use Doctrine\Common\Collections\Collection;

/**
 * @template TTitleTranslation of TitleTranslationInterface
 */
interface TranslatableTitleInterface
{
    /**
     * @return Collection<int, TTitleTranslation>
     */
    public function getTitleTranslations(): Collection;

    public function addTitleTranslation(
        TitleTranslationInterface $titleTranslation,
    ): static;

    public function removeTitleTranslation(
        TitleTranslationInterface $titleTranslation,
    ): static;
}
