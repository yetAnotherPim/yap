<?php

declare(strict_types=1);

namespace App\PluginManager\Event\WorkflowEvent\EventListener;

use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class PluginWorkflowEventListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheClearer $cacheClearer,
        #[Target('pluginLogger')]
        private readonly LoggerInterface $pluginLogger,
    ) {
    }

    /**
     * @throws PluginWorkflowException
     */
    #[AsEventListener(priority: 25)]
    public function onActivate(PluginActivateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        try {
            $pluginInstance->activate($this->entityManager);
        } catch (\Throwable $exception) {
            $this->pluginLogger->error(
                'An error occurred at {pluginInstance}::activate()',
                [
                    'pluginInstance' => \get_class($pluginInstance),
                    'message' => $exception->getMessage(),
                    'exception' => \get_class($exception),
                ]
            );

            throw new PluginWorkflowException(Action::ACTIVATE, $event->getPluginEntity()->getName(), $exception->getMessage(), $exception->getCode(), $exception);
        }

        $pluginEntity
            ->setActive(true)
            ->setInstalledVersion($pluginInstance->getVersion());

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    /**
     * @throws PluginWorkflowException
     */
    #[AsEventListener(priority: 25)]
    public function onDeactivate(PluginDeactivateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        try {
            $pluginInstance->deactivate($this->entityManager);
        } catch (\Throwable $exception) {
            $this->pluginLogger->error(
                'An error occurred at {pluginInstance}::deactivate()',
                [
                    'pluginInstance' => \get_class($pluginInstance),
                    'message' => $exception->getMessage(),
                    'exception' => \get_class($exception),
                ]
            );

            throw new PluginWorkflowException(Action::DEACTIVATE, $event->getPluginEntity()->getName(), $exception->getMessage(), $exception->getCode(), $exception);
        }

        $pluginEntity
            ->setActive(false);

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    /**
     * @throws PluginWorkflowException
     */
    #[AsEventListener(priority: 25)]
    public function onUpdate(PluginUpdateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        if (null === $pluginEntity->getInstalledVersion()) {
            $this->pluginLogger->error(
                'Plugin {pluginInstance} is not installed',
                [
                    'pluginInstance' => \get_class($pluginInstance),
                    'toVersion' => $pluginInstance->getVersion(),
                ]
            );

            throw new PluginWorkflowException(Action::UPDATE, $event->getPluginEntity()->getName(), 'Plugin is not installed');
        }

        $event->setFromVersion($pluginEntity->getInstalledVersion());

        try {
            $pluginInstance->update($pluginEntity->getInstalledVersion(), $this->entityManager);
        } catch (\Throwable $exception) {
            $this->pluginLogger->error(
                'An error occurred at {pluginInstance}::update()',
                [
                    'pluginInstance' => \get_class($pluginInstance),
                    'fromVersion' => $event->getFromVersion(),
                    'toVersion' => $pluginInstance->getVersion(),
                    'message' => $exception->getMessage(),
                    'exception' => \get_class($exception),
                ]
            );

            throw new PluginWorkflowException(Action::UPDATE, $event->getPluginEntity()->getName(), $exception->getMessage(), $exception->getCode(), $exception);
        }

        $pluginEntity
            ->setActive(true)
            ->setInstalledVersion($pluginInstance->getVersion());

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    private function finalise(): void
    {
        $this->cacheClearer->clear();
    }
}
