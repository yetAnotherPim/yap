<?php

declare(strict_types=1);

namespace App\Tests\unit\Localisation\Event\EventListener;

use App\Entity\Locale\Locale;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[CoversClass(LocaleListener::class)]
#[UsesClass(DefaultLocaleIsNotDefinedException::class)]
class LocaleListenerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepositoryMock;

    private LocaleListener $listener;

    public function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);

        $this->listener = new LocaleListener($this->localeRepositoryMock);

        parent::setUp();
    }

    public function testOnKernelRequestReturnsWhenNoPreviousSession(): void
    {
        $request = Request::create('/');
        // No session is set, so hasPreviousSession() will return false

        $event = \Mockery::mock(RequestEvent::class);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(true);

        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestUsesLocaleFromSession(): void
    {
        $session = \Mockery::mock(Session::class);
        $session
            ->shouldReceive('getName')
            ->andReturn('MOCK_SESSION');

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturn('de_DE');

        $request = Request::create('/');
        $request->cookies->set('MOCK_SESSION', 'session_id');
        $request->setSession($session);

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(true);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onKernelRequest($event);

        $this->assertEquals('de_DE', $request->getLocale());
    }

    public function testOnKernelRequestUsesDefaultLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')
            ->once()
            ->andReturn('en');

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andReturn($locale);

        $session = \Mockery::mock(Session::class);
        $session->shouldReceive('getName')->andReturn('MOCK_SESSION');

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturnNull();

        $request = Request::create('/');
        $request->cookies->set('MOCK_SESSION', 'session_id');
        $request->setSession($session);

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(true);

        $this->listener->onKernelRequest($event);

        $this->assertEquals('en', $request->getLocale());
    }

    public function testOnKernelRequestReturnsWhenStateless(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_stateless', true);

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(true);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onKernelRequest($event);

        // Locale should remain default 'en'
        $this->assertEquals('en', $request->getLocale());
    }

    public function testOnKernelRequestReturnsWhenSubrequest(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_stateless', true);

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(false);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onKernelRequest($event);

        // Locale should remain default 'en'
        $this->assertEquals('en', $request->getLocale());
    }

    public function testOnKernelRequestAddsFlashMessageWhenNoDefaultLocaleExists(): void
    {
        $flashBag = \Mockery::mock(FlashBagInterface::class);

        $exception = new DefaultLocaleIsNotDefinedException();

        $flashBag->shouldReceive('add')
            ->once()
            ->with('error', $exception->getMessage());

        $session = \Mockery::mock(Session::class);
        $session->shouldReceive('getName')->andReturn('MOCK_SESSION');

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturnNull();

        $session->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBag);

        $request = Request::create('/');
        $request->cookies->set('MOCK_SESSION', 'session_id');
        $request->setSession($session);

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMainRequest')
            ->once()
            ->andReturn(true);

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andThrow($exception);

        $this->listener->onKernelRequest($event);
    }
}
