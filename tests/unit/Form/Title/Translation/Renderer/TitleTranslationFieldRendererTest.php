<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Title\Translation\Renderer;

use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Resolver\TitleTranslationResolver;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(TitleTranslationFieldRenderer::class)]
class TitleTranslationFieldRendererTest extends MockeryTestCase
{
    /** @var LocaleRepository&Mockery\MockInterface */
    private LocaleRepository $localeRepositoryMock;

    /** @var TitleTranslationResolver&Mockery\MockInterface */
    private TitleTranslationResolver $titleTranslationResolverMock;

    private TitleTranslationFieldRenderer $renderer;

    protected function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);
        $this->titleTranslationResolverMock = \Mockery::mock(TitleTranslationResolver::class);

        $this->renderer = new TitleTranslationFieldRenderer(
            $this->localeRepositoryMock,
            $this->titleTranslationResolverMock,
        );

        parent::setUp();
    }

    public function testRenderReturnsEmptyArrayWhenNoLocalesAreAvailable(): void
    {
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection());

        $fields = $this->renderer->render(
            null,
            new TranslatableMessage('field_type.labels.title', [], 'admin'),
        );

        $this->assertSame([], $fields);
    }

    public function testRenderCreatesFieldsForAvailableLocalesWithoutEntity(): void
    {
        $locale1 = \Mockery::mock(Locale::class);
        $locale2 = \Mockery::mock(Locale::class);

        $locale1
            ->shouldReceive('getId')
            ->andReturn('de_DE');

        $locale2
            ->shouldReceive('getId')
            ->andReturn('en_GB');

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale1, $locale2]));

        $label = new TranslatableMessage(
            'field_type.labels.title',
            [],
            'admin',
        );

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale1);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale2);

        $fields = $this->renderer->render(null, $label);

        $this->assertCount(4, $fields);

        $this->assertInstanceOf(FormField::class, $fields[0]);

        $this->assertInstanceOf(LocaleField::class, $fields[1]);
        $this->assertSame(
            'translation_locale',
            $fields[1]->getAsDto()->getProperty(),
        );

        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertSame(
            'title-de_DE',
            $fields[2]->getAsDto()->getProperty(),
        );

        $this->assertInstanceOf(TextField::class, $fields[3]);
        $this->assertSame(
            'title-en_GB',
            $fields[3]->getAsDto()->getProperty(),
        );
    }

    public function testRenderUsesExistingTranslationsAsFieldData(): void
    {
        $deLocale = \Mockery::mock(Locale::class);
        $deLocale
            ->shouldReceive('getId')
            ->andReturn('de_DE');

        $enLocale = \Mockery::mock(Locale::class);
        $enLocale
            ->shouldReceive('getId')
            ->andReturn('en_GB');

        $frLocale = \Mockery::mock(Locale::class);
        $frLocale
            ->shouldReceive('getId')
            ->andReturn('fr_FR');

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$deLocale, $enLocale, $frLocale]));

        $deTranslation = \Mockery::mock(TitleTranslationInterface::class);
        $deTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Deutscher Titel');

        $enTranslation = \Mockery::mock(TitleTranslationInterface::class);
        $enTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('English title');

        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $deLocale)
            ->andReturn($deTranslation);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $enLocale)
            ->andReturn($enTranslation);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $frLocale)
            ->andReturn(null);

        $fields = $this->renderer->render(
            $entity,
            new TranslatableMessage('field_type.labels.title', [], 'admin'),
        );

        $this->assertCount(5, $fields);

        $this->assertSame(
            'Deutscher Titel',
            $fields[2]->getAsDto()->getFormTypeOption('data'),
        );
        $this->assertSame(
            'English title',
            $fields[3]->getAsDto()->getFormTypeOption('data'),
        );
        $this->assertNull(
            $fields[4]->getAsDto()->getFormTypeOption('data'),
        );
    }

    public function testRenderConfiguresLocaleField(): void
    {
        $locale1 = \Mockery::mock(Locale::class);
        $locale2 = \Mockery::mock(Locale::class);
        $locale3 = \Mockery::mock(Locale::class);

        $locale1
            ->shouldReceive('getId')
            ->andReturn('de_DE');

        $locale2
            ->shouldReceive('getId')
            ->andReturn('en_GB');

        $locale3
            ->shouldReceive('getId')
            ->andReturn('fr_FR');

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale1, $locale2, $locale3]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale1)
            ->andReturn(null);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale2)
            ->andReturn(null);

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale3)
            ->andReturn(null);

        $fields = $this->renderer->render(null, 'Title');

        $localeField = $fields[1]->getAsDto();

        $this->assertSame(
            'translation_locale',
            $localeField->getProperty(),
        );
        $this->assertSame(
            '',
            $localeField->getFormTypeOption('data'),
        );
        $this->assertFalse(
            $localeField->getFormTypeOption('mapped'),
        );

        $htmlAttributes = $localeField->getHtmlAttributes();

        $this->assertSame(
            'title-translation',
            $htmlAttributes['data-controller'],
        );
        $this->assertSame(
            'change->title-translation#update',
            $htmlAttributes['data-action'],
        );
    }

    public function testRenderConfiguresTranslationFields(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $locale
            ->shouldReceive('getId')
            ->andReturn('de_DE');

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with(null, $locale)
            ->andReturn(null);

        $fields = $this->renderer->render(null, 'Title');

        $field = $fields[2]->getAsDto();

        $this->assertSame('title-de_DE', $field->getProperty());
        $this->assertNull($field->getFormTypeOption('data'));
        $this->assertFalse($field->getFormTypeOption('mapped'));
        $this->assertSame(
            [
                'data-title-translation-locale' => 'de_DE',
            ],
            $field->getFormTypeOption('row_attr'),
        );
    }
}
