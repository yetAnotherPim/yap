<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_types\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\FieldTypeRepository;
use App\Repository\FieldTypeTitleTranslationRepository;
use App\Repository\LocaleRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\Field\Type\Translation\FieldTypeTitleTranslationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(FieldType::class)]
#[CoversClass(FieldTypeRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(FieldTypeTitleTranslation::class)]
#[UsesClass(FieldTypeTitleTranslationRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
class FieldTypeApiTest extends ApiTestCase
{
    use AuthTrait;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private FieldTypeTitleTranslationRepository $fieldTypeTitleTranslationRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $fieldTypeTitleTranslationRepository = $container->get(FieldTypeTitleTranslationRepository::class);
        $this->assertInstanceOf(FieldTypeTitleTranslationRepository::class, $fieldTypeTitleTranslationRepository);

        $this->fieldTypeTitleTranslationRepository = $fieldTypeTitleTranslationRepository;

        $this->auth();

        parent::setUp();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatch(): void
    {
        $fieldType = FieldTypeFactory::createOne(['title' => 'Something']);

        $response = $this->client->request(
            'PATCH',
            '/api/field_types/'.$fieldType->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "title":"Product name"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Product name']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatchRemovingTranslation(): void
    {
        $fieldType = FieldTypeFactory::createOne(
            [
                'title' => 'Something',
                'translations' => [
                    FieldTypeTitleTranslationFactory::new([
                        'locale' => LocaleFactory::findOrCreate(['id' => 'en_GB']),
                    ]),
                    FieldTypeTitleTranslationFactory::new([
                        'locale' => LocaleFactory::findOrCreate(['id' => 'de_DE']),
                    ]),
                ],
            ]
        );

        $this->assertCount(2, $this->fieldTypeTitleTranslationRepository->findAll());

        $response = $this->client->request(
            'PATCH',
            '/api/field_types/'.$fieldType->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "translations":[]
                            }',
            ]
        );

        $this->assertCount(0, $this->fieldTypeTitleTranslationRepository->findAll());

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Something']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/field_types/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
