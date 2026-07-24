<?php

declare(strict_types=1);

namespace App\Entity\Plugin;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\PluginRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
#[Index(name: 'index_plugin_name', fields: ['name'])]
#[ORM\UniqueConstraint(name: 'index_plugin_name', fields: ['name'])]
#[ORM\UniqueConstraint(name: 'index_plugin_namespace', fields: ['namespace'])]
#[ORM\Cache()]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['plugin:read']],
        ),
        new GetCollection(
            openapi: new Operation(tags: ['Plugin'], summary: 'Clear application cache for reloading plugins'),
            uriTemplate: 'refresh/plugins',
            provider: PluginsRefreshProvider::class,
            normalizationContext: ['groups' => ['plugin:refresh']],
        ),
        new Post(
            routeName: 'perform_plugin_workflow',
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'name',
                        in: 'path',
                        schema: ['type' => 'string'],
                        required: true
                    ),
                    new Parameter(
                        name: 'action',
                        in: 'path',
                        schema: ['type' => 'string', 'enum' => ['update', 'activate', 'deactivate']],
                        required: true
                    ),
                ],
                requestBody: new RequestBody(content: new \ArrayObject()),
                responses: [
                    201 => new Response('Unused', new \ArrayObject()),
                    202 => new Response(
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                ],
                            ],
                        ]
                        )
                    ),
                    400 => new Response(
                        description: 'Invalid action given',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'errorMessage' => ['type' => 'string'],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                    'errorMessage' => 'Invalid action given',
                                ],
                            ],
                        ]
                        )
                    ),
                    412 => new Response(
                        description: 'Plugin invalid or not found',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'errorMessage' => ['type' => 'string'],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                    'errorMessage' => 'Plugin invalid or not found',
                                ],
                            ],
                        ]
                        )
                    ),
                    422 => new Response(
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'errorMessage' => ['type' => 'string'],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                    'errorMessage' => 'Something went wrong',
                                ],
                            ],
                        ]
                        )
                    ),
                ],
                summary: 'Activate/Deactivate & Update Plugins'
            ),
            read: false, name: 'workflow'
        ),
    ]
)]
class Plugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private Uuid $id;

    #[Groups(['plugin:refresh', 'plugin:read'])]
    #[ORM\Column(length: 255)]
    private string $name;
    #[Groups(['plugin:refresh', 'plugin:read'])]
    #[ORM\Column(length: 255)]
    private string $namespace;

    #[Groups(['plugin:refresh', 'plugin:read'])]
    #[ORM\Column]
    private bool $active = false;

    #[Groups(['plugin:refresh', 'plugin:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $installedVersion = null;

    #[Groups(['plugin:refresh'])]
    #[ORM\Column(length: 255, nullable: false)]
    private string $version = '';

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

    #[Groups(['plugin:refresh'])]
    public function isUpdatable(): bool
    {
        if (null === $this->getInstalledVersion()) {
            return false;
        }

        return version_compare($this->getVersion(), $this->getInstalledVersion(), 'gt');
    }
}
