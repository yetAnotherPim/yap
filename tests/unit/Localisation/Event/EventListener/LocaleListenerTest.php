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
        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('hasPreviousSession')
            ->once()
            ->andReturnFalse();

        $event = \Mockery::mock(RequestEvent::class);
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

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturn('de_DE');

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('hasPreviousSession')
            ->once()
            ->andReturnTrue();

        $request->shouldReceive('getSession')
            ->twice()
            ->andReturn($session);

        $request->shouldReceive('setLocale')
            ->once()
            ->with('de_DE');

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onKernelRequest($event);
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

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturnNull();

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('hasPreviousSession')
            ->once()
            ->andReturnTrue();

        $request->shouldReceive('getSession')
            ->twice()
            ->andReturn($session);

        $request->shouldReceive('setLocale')
            ->once()
            ->with('en');

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestAddsFlashMessageWhenNoDefaultLocaleExists(): void
    {
        $flashBag = \Mockery::mock(FlashBagInterface::class);

        $exception = new DefaultLocaleIsNotDefinedException();

        $flashBag->shouldReceive('add')
            ->once()
            ->with('error', $exception->getMessage());

        $session = \Mockery::mock(Session::class);

        $session->shouldReceive('get')
            ->once()
            ->with('_locale')
            ->andReturnNull();

        $session->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBag);

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('hasPreviousSession')
            ->once()
            ->andReturnTrue();

        $request->shouldReceive('getSession')
            ->times(2)
            ->andReturn($session);

        $request->shouldNotReceive('setLocale');

        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andThrow($exception);

        $this->listener->onKernelRequest($event);
    }
}
