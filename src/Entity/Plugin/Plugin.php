<?php

declare(strict_types=1);

namespace App\Entity\Plugin;

use App\Repository\PluginRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
#[Index(name: 'index_plugin_name', fields: ['name'])]
#[ORM\UniqueConstraint(name: 'plugin_name', fields: ['name'])]
#[ORM\UniqueConstraint(name: 'plugin_namespace', fields: ['namespace'])]
#[ORM\Cache()]
class Plugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;
    #[ORM\Column(length: 255)]
    private string $namespace;

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $installedVersion = null;
    private string $version;

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): Plugin
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getInstalledVersion(): ?string
    {
        return $this->installedVersion;
    }

    public function setInstalledVersion(string $installedVersion): Plugin
    {
        $this->installedVersion = $installedVersion;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function isUpdatable(): bool
    {
        if (null === $this->getInstalledVersion()) {
            return false;
        }

        return version_compare($this->getVersion(), $this->getInstalledVersion(), 'gt');
    }
}
