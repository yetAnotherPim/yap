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

#[ORM\Entity(repositoryClass: LocaleRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Delete(),
])]
class Locale
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 8)]
        private string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    // TODO: remove translations on deletion -> api-test + integration test
    //create FieldValue and FieldTitleTranslations -> check repo
    //delete locale, check via that other translations still exist
}
