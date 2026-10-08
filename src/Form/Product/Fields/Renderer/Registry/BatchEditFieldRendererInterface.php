<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Registry;

interface BatchEditFieldRendererInterface
{
    public function getBatchEditFormType(
    ): string;

    /**
     * @return array<string, mixed>
     */
    public function getBatchEditOptions(
    ): array;
}
