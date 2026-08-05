<?php

declare(strict_types=1);

namespace App\Doctrine\Event\EventListener;

use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Abstract\TimestampableEntity;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

#[AsDoctrineListener(event: Events::onFlush)]
class RelatedTimestampUpdater
{
    /** @var array<int, string> */
    private array $updatedEntities = [];

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $this->updatedEntities = [];

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            if ($entity instanceof RelatedTimestampableEntity) {
                $this->updateRelatedRecursive($entity, $uow, $em);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof RelatedTimestampableEntity) {
                $this->updateRelatedRecursive($entity, $uow, $em);
            }
        }
    }

    private function updateRelatedRecursive(RelatedTimestampableEntity $entity, UnitOfWork $uow, EntityManagerInterface $em): void
    {
        $oid = spl_object_hash($entity);
        if (\in_array($oid, $this->updatedEntities, true)) {
            return;
        }
        $this->updatedEntities[] = $oid;

        $updatableRelations = $entity->getUpdatableRelations();

        foreach ($updatableRelations as $type => $relation) {
            $getter = 'get'.ucfirst($relation);
            if (!method_exists($entity, $getter)) {
                continue;
            }
            $target = $entity->$getter();

            if ($target instanceof TimestampableEntity && $target instanceof $type) {
                if ($uow->isScheduledForDelete($target) || UnitOfWork::STATE_MANAGED !== $uow->getEntityState($target)) {
                    continue;
                }

                $target->setUpdatedAt(new \DateTimeImmutable());

                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata(\get_class($target)),
                    $target
                );

                if ($target instanceof RelatedTimestampableEntity) {
                    $this->updateRelatedRecursive($target, $uow, $em);
                }
            }
        }
    }
}
