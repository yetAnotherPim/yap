<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\ProductType\DefaultTranslation\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\ProductTypeFieldConfigurationDefaultTranslationResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ProductTypeFieldConfigurationDefaultTranslationResolver::class)]
#[UsesClass(ProductTypeFieldConfigurationDefaultTranslation::class)]
final class ProductTypeFieldConfigurationDefaultTranslationResolverTest extends MockeryTestCase
{
    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private Locale&MockInterface $locale;

    private ProductTypeFieldConfigurationDefaultTranslationResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class,
        );

        $this->locale = \Mockery::mock(
            Locale::class,
        );

        $this->resolver =
            new ProductTypeFieldConfigurationDefaultTranslationResolver();
    }

    public function testResolveReturnsExistingTranslation(): void
    {
        $translation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $existingLocale = \Mockery::mock(Locale::class);

        $this->locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $existingLocale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($existingLocale);

        $this->configuration
            ->shouldReceive(
                'getProductTypeFieldConfigurationDefaultTranslations',
            )
            ->once()
            ->andReturn(
                new ArrayCollection([
                    $translation,
                ]),
            );

        $this->configuration
            ->shouldNotReceive(
                'addProductTypeFieldConfigurationDefaultTranslation',
            );

        $result = $this->resolver->resolve(
            $this->configuration,
            $this->locale,
        );

        $this->assertSame(
            $translation,
            $result,
        );
    }

    public function testResolveCreatesTranslationWhenNoneExists(): void
    {
        $this->configuration
            ->shouldReceive(
                'getProductTypeFieldConfigurationDefaultTranslations',
            )
            ->once()
            ->andReturn(new ArrayCollection());

        $this->configuration
            ->shouldReceive(
                'addProductTypeFieldConfigurationDefaultTranslation',
            )
            ->once()
            ->with(
                \Mockery::on(
                    function (
                        ProductTypeFieldConfigurationDefaultTranslation $translation,
                    ): bool {
                        return $translation->getLocale() === $this->locale
                            && $translation
                                ->getProductTypeFieldConfiguration()
                            === $this->configuration;
                    },
                ),
            )
            ->andReturnSelf();

        $result = $this->resolver->resolve(
            $this->configuration,
            $this->locale,
        );

        $this->assertSame(
            $this->locale,
            $result->getLocale(),
        );

        $this->assertSame(
            $this->configuration,
            $result->getProductTypeFieldConfiguration(),
        );
    }

    public function testResolveCreatesTranslationWhenLocaleDoesNotMatch(): void
    {
        $translation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $existingLocale = \Mockery::mock(Locale::class);

        $this->locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $existingLocale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('es_ES');

        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($existingLocale);

        $this->configuration
            ->shouldReceive(
                'getProductTypeFieldConfigurationDefaultTranslations',
            )
            ->once()
            ->andReturn(
                new ArrayCollection([
                    $translation,
                ]),
            );

        $this->configuration
            ->shouldReceive(
                'addProductTypeFieldConfigurationDefaultTranslation',
            )
            ->once()
            ->with(
                \Mockery::type(
                    ProductTypeFieldConfigurationDefaultTranslation::class,
                ),
            )
            ->andReturnSelf();

        $result = $this->resolver->resolve(
            $this->configuration,
            $this->locale,
        );

        $this->assertNotSame(
            $translation,
            $result,
        );

        $this->assertSame(
            $this->locale,
            $result->getLocale(),
        );

        $this->assertSame(
            $this->configuration,
            $result->getProductTypeFieldConfiguration(),
        );
    }
}
