<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Entity;

use App\Entity\Abstract\TimestampableEntity;
use App\plugins\Feeds\Enum\Schedule;
use App\plugins\Feeds\Repository\FeedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FeedRepository::class)]
class Feed extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(enumType: Schedule::class)]
    private Schedule $schedule = Schedule::DAILY;

    #[ORM\Column(nullable: true)]
    private ?array $productFilters = null;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $head = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footer = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $latest = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Feed
    {
        $this->active = $active;

        return $this;
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): Feed
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getProductFilters(): ?array
    {
        return $this->productFilters;
    }

    public function setProductFilters(?array $productFilters): static
    {
        $this->productFilters = $productFilters;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getHead(): ?string
    {
        return $this->head;
    }

    public function setHead(?string $head): static
    {
        $this->head = $head;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): static
    {
        $this->footer = $footer;

        return $this;
    }

    public function getLatest(): ?\DateTimeImmutable
    {
        return $this->latest;
    }

    public function setLatest(?\DateTimeImmutable $latest): static
    {
        $this->latest = $latest;

        return $this;
    }
}
