<?php

declare(strict_types=1);

namespace App\Localisation\Event\EventListener;

use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 20)]
class LocaleListener
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (false === $event->isMainRequest()) {
            return;
        }

        if ($request->attributes->get('_stateless', false)) {
            return;
        }

        if (false === $request->hasPreviousSession()) {
            return;
        }

        /** @phpstan-var Session $session */
        $session = $request->getSession();
        $locale = $request->getSession()->get('_locale');

        if (false === \is_string($locale)) {
            try {
                $locale = $this->localeRepository->getDefaultLocale()->getId();
            } catch (DefaultLocaleIsNotDefinedException $exception) {
                $session->getFlashBag()->add('error', $exception->getMessage());

                return;
            }
        }

        $request->setLocale($locale);
    }
}
