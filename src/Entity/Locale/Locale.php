<?php

declare(strict_types=1);

namespace App\Entity\Locale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\LocaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocaleRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Delete(),
])]
class Locale
{
    #[ORM\Id]
    #[ORM\Column(length: 8)]
    #[Assert\Length(max: 8)]
    private string $id;

    #[ORM\Column(name: 'is_default', options: ['default' => 0])]
    private bool $default = false;

    #[ORM\Column(
        options: ['default' => 0],
    )]
    private bool $contentTranslationEnabled = false;

    public function setId(string $id): Locale
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): Locale
    {
        $this->default = $default;

        return $this;
    }

    public function isContentTranslationEnabled(): bool
    {
        return $this->contentTranslationEnabled;
    }

    public function setContentTranslationEnabled(bool $contentTranslationEnabled): Locale
    {
        $this->contentTranslationEnabled = $contentTranslationEnabled;

        return $this;
    }

    // TODO: remove translations on deletion -> api-test + integration test
    // create FieldValue and FieldTitleTranslations -> check repo
    // delete locale, check via that other translations still exist
}
