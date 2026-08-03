<?php

declare(strict_types=1);

namespace App\Tests\unit\Localisation\Event\EventListener;

use App\Entity\Locale\Locale;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[CoversClass(LoginSuccessListener::class)]
#[UsesClass(DefaultLocaleIsNotDefinedException::class)]
class LoginSuccessListenerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepositoryMock;

    private LoginSuccessListener $listener;

    public function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);

        $this->listener = new LoginSuccessListener($this->localeRepositoryMock);

        parent::setUp();
    }

    public function testOnLoginSuccessUsesChosenLocale(): void
    {
        $session = \Mockery::mock(Session::class);
        $session
            ->shouldReceive('set')
            ->once()
            ->with('_locale', 'de_DE');
        $session
            ->shouldReceive('getFlashBag')
            ->never();

        $request = Request::create('/login', 'POST', [
            '_locale' => 'de_DE',
        ]);
        $request->setSession($session);

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onLoginSuccess($event);

        $this->assertSame('de_DE', $request->getLocale());
    }

    public function testOnLoginSuccessUsesDefaultLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')
            ->once()
            ->andReturn('en_GB');

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andReturn($locale);

        $session = \Mockery::mock(Session::class);
        $session
            ->shouldReceive('set')
            ->once()
            ->with('_locale', 'en_GB');

        $request = Request::create('/login', 'POST');
        $request->setSession($session);

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event
            ->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->listener->onLoginSuccess($event);

        $this->assertSame('en_GB', $request->getLocale());
    }

    public function testOnLoginSuccessAddsFlashMessageWhenNoDefaultLocaleExists(): void
    {
        $exception = new DefaultLocaleIsNotDefinedException();

        $flashBag = \Mockery::mock(FlashBagInterface::class);
        $flashBag->shouldReceive('add')
            ->once()
            ->with('error', $exception->getMessage());

        $session = \Mockery::mock(Session::class);
        $session->shouldReceive('getFlashBag')
            ->once()
            ->andReturn($flashBag);

        $session->shouldNotReceive('set');

        $request = Request::create('/login', 'POST');
        $request->setSession($session);

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andThrow($exception);

        $this->listener->onLoginSuccess($event);

        $this->assertSame('en', $request->getLocale());
    }

    public function testOnLoginSuccessFallsBackWhenLocaleIsNotString(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')
            ->once()
            ->andReturn('fr');

        $this->localeRepositoryMock
            ->shouldReceive('getDefaultLocale')
            ->once()
            ->andReturn($locale);

        $session = \Mockery::mock(Session::class);
        $session->shouldReceive('set')
            ->once()
            ->with('_locale', 'fr');

        $request = Request::create('/login', 'POST');
        $request->request->set('_locale', 1);
        $request->setSession($session);

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->listener->onLoginSuccess($event);

        $this->assertSame('fr', $request->getLocale());
    }

    public function testOnLoginSuccessReturnsWhenStateless(): void
    {
        $request = Request::create('/login', 'POST');
        $request->attributes->set('_stateless', true);

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onLoginSuccess($event);

        $this->assertSame('en', $request->getLocale());
    }

    public function testOnLoginSuccessReturnsWhenNoSession(): void
    {
        $request = Request::create('/login', 'POST');
        // No session is set, so hasSession() will return false

        $event = \Mockery::mock(LoginSuccessEvent::class);
        $event->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $this->localeRepositoryMock
            ->shouldNotReceive('getDefaultLocale');

        $this->listener->onLoginSuccess($event);

        $this->assertSame('en', $request->getLocale());
    }
}
