<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Title\Translation\Renderer;

use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
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

    private TitleTranslationFieldRenderer $renderer;

    protected function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);

        $this->renderer = new TitleTranslationFieldRenderer(
            $this->localeRepositoryMock,
        );

        parent::setUp();
    }

    public function testRenderReturnsEmptyArrayWhenNoLocalesAreAvailable(): void
    {
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn([]);

        $fields = $this->renderer->render(
            null,
            new TranslatableMessage('field_type.labels.title', [], 'admin'),
        );

        $this->assertSame([], $fields);
    }

    public function testRenderCreatesFieldsForAvailableLocalesWithoutEntity(): void
    {
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn(['de_DE', 'en_GB']);

        $label = new TranslatableMessage(
            'field_type.labels.title',
            [],
            'admin',
        );

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
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn(['de_DE', 'en_GB', 'fr_FR']);

        $deLocale = \Mockery::mock(Locale::class);
        $deLocale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $enLocale = \Mockery::mock(Locale::class);
        $enLocale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('en_GB');

        $deTranslation = \Mockery::mock(TitleTranslationInterface::class);
        $deTranslation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($deLocale);
        $deTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Deutscher Titel');

        $enTranslation = \Mockery::mock(TitleTranslationInterface::class);
        $enTranslation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($enLocale);
        $enTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('English title');

        $entity = \Mockery::mock(TranslatableTitleInterface::class);
        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(new ArrayCollection([
                $deTranslation,
                $enTranslation,
            ]));

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
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn(['de_DE', 'en_GB']);

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
        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn(['de_DE']);

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
