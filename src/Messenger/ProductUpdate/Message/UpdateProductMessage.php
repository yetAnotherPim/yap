<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message;

use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductMessage
{
    /** @var Collection<int, ProductField> */
    #[Assert\Valid]
    private Collection $fields;

    public function __construct(
        #[Assert\NotBlank]
        private string $productId,
    ) {
        $this->fields = new ArrayCollection();
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return Collection<int, ProductField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @param Collection<int, ProductField> $fields
     */
    public function setFields(Collection $fields): self
    {
        $this->fields = $fields;

        return $this;
    }
}
