<?php

declare(strict_types=1);

namespace App\Localisation\Event\EventListener;

use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
class LoginSuccessListener
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        /** @phpstan-var Session $session */
        $session = $request->getSession();
        $chosenLocale = $request->request->get('_locale');

        if (false === \is_string($chosenLocale)) {
            try {
                $chosenLocale = $this->localeRepository->getDefaultLocale()->getId();
            } catch (DefaultLocaleIsNotDefinedException $exception) {
                $session->getFlashBag()->add('error', $exception->getMessage());

                return;
            }
        }

        $session->set('_locale', $chosenLocale);
        $request->setLocale($chosenLocale);
    }
}
