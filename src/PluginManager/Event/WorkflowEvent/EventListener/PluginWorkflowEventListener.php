<?php

declare(strict_types=1);

namespace App\PluginManager\Event\WorkflowEvent\EventListener;

use App\Kernel;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class PluginWorkflowEventListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Kernel $kernel,
    ) {
    }

    #[AsEventListener(priority: 25)]
    public function onActivate(PluginActivateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        try {
            $pluginInstance->activate($this->entityManager);
        } catch (\Throwable $exception) {
            // TODO: plugin logger
            return;
        }

        $pluginEntity
            ->setActive(true)
            ->setInstalledVersion($pluginInstance->getVersion());

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    #[AsEventListener(priority: 25)]
    public function onDeactivate(PluginDeactivateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        try {
            $pluginInstance->deactivate($this->entityManager);
        } catch (\Throwable $exception) {
            // TODO: plugin logger
            return;
        }

        $pluginEntity
            ->setActive(false);

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    #[AsEventListener(priority: 25)]
    public function onUpdate(PluginUpdateEvent $event): void
    {
        $pluginInstance = $event->getPluginInstance();
        $pluginEntity = $event->getPluginEntity();

        if (null === $pluginEntity->getInstalledVersion()) {
            return;
        }

        try {
            $pluginInstance->update($pluginEntity->getInstalledVersion(), $this->entityManager);
        } catch (\Throwable $exception) {
            // TODO: plugin logger
            return;
        }

        $pluginEntity
            ->setActive(true)
            ->setInstalledVersion($pluginInstance->getVersion());

        $this->entityManager->persist($pluginEntity);
        $this->entityManager->flush();

        $this->finalise();
    }

    public function finalise(): void
    {
        $application = new Application($this->kernel);
        $application->doRun(new ArrayInput(['cache:clear']), new NullOutput());
    }
}
