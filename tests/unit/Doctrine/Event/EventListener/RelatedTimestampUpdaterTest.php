<?php

declare(strict_types=1);

namespace App\Tests\unit\Doctrine\Event\EventListener;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Abstract\TimestampableEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RelatedTimestampUpdater::class)]
#[UsesClass(RelatedTimestampableEntity::class)]
#[UsesClass(TimestampableEntity::class)]
class RelatedTimestampUpdaterTest extends MockeryTestCase
{
    private RelatedTimestampUpdater $updater;

    private EntityManagerInterface&MockInterface $entityManager;

    private UnitOfWork&MockInterface $unitOfWork;

    protected function setUp(): void
    {
        parent::setUp();

        $this->updater = new RelatedTimestampUpdater();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->unitOfWork = \Mockery::mock(UnitOfWork::class);

        $this->entityManager
            ->shouldReceive('getUnitOfWork')
            ->once()
            ->andReturn($this->unitOfWork);
    }

    public function testUpdatesRelatedEntityForScheduledInsertion(): void
    {
        $target = new TestTimestampableEntity();
        $entity = new TestRelatedTimestampableEntity($target);

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->expectTargetToBeUpdated($target);

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testUpdatesRelatedEntityForScheduledUpdate(): void
    {
        $target = new TestTimestampableEntity();
        $entity = new TestRelatedTimestampableEntity($target);

        $this->expectScheduledEntities(
            updates: [$entity],
        );

        $this->expectTargetToBeUpdated($target);

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testUpdatesRelatedEntityForScheduledDeletion(): void
    {
        $target = new TestTimestampableEntity();
        $entity = new TestRelatedTimestampableEntity($target);

        $this->expectScheduledEntities(
            deletions: [$entity],
        );

        $this->expectTargetToBeUpdated($target);

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testIgnoresEntityWhichIsNotRelatedTimestampable(): void
    {
        $entity = new TimestampableEntity();

        $this->expectScheduledEntities(
            insertions: [$entity],
            updates: [$entity],
            deletions: [$entity],
        );

        $this->unitOfWork
            ->shouldNotReceive('isScheduledForDelete');

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testIgnoresRelationWhenGetterDoesNotExist(): void
    {
        $entity = new TestRelatedTimestampableEntity(
            target: null,
            updatableRelations: [
                TestTimestampableEntity::class => 'doesNotExist',
            ],
        );

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->unitOfWork
            ->shouldNotReceive('isScheduledForDelete');

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testIgnoresRelationWhenTargetIsNotTimestampable(): void
    {
        $target = new \stdClass();

        $entity = new TestRelatedTimestampableEntity(
            target: $target,
            updatableRelations: [
                \stdClass::class => 'target',
            ],
        );

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->unitOfWork
            ->shouldNotReceive('isScheduledForDelete');

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testIgnoresRelationWhenTargetDoesNotMatchConfiguredType(): void
    {
        $target = new TestTimestampableEntity();

        $entity = new TestRelatedTimestampableEntity(
            target: $target,
            updatableRelations: [
                TestRelatedTimestampableEntity::class => 'target',
            ],
        );

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->unitOfWork
            ->shouldNotReceive('isScheduledForDelete');

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testDoesNotUpdateTargetScheduledForDeletion(): void
    {
        $target = new TestTimestampableEntity();
        $entity = new TestRelatedTimestampableEntity($target);

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->unitOfWork
            ->shouldReceive('isScheduledForDelete')
            ->once()
            ->with($target)
            ->andReturnTrue();

        $this->unitOfWork
            ->shouldNotReceive('getEntityState');

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testDoesNotUpdateUnmanagedTarget(): void
    {
        $target = new TestTimestampableEntity();
        $entity = new TestRelatedTimestampableEntity($target);

        $this->expectScheduledEntities(
            insertions: [$entity],
        );

        $this->unitOfWork
            ->shouldReceive('isScheduledForDelete')
            ->once()
            ->with($target)
            ->andReturnFalse();

        $this->unitOfWork
            ->shouldReceive('getEntityState')
            ->once()
            ->with($target)
            ->andReturn(UnitOfWork::STATE_NEW);

        $this->entityManager
            ->shouldNotReceive('getClassMetadata');

        $this->unitOfWork
            ->shouldNotReceive('recomputeSingleEntityChangeSet');

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testUpdatesRelatedEntitiesRecursively(): void
    {
        $lastTarget = new TestTimestampableEntity();

        $middleTarget = new TestRelatedTimestampableEntity(
            $lastTarget,
        );

        $entity = new TestRelatedTimestampableEntity(
            $middleTarget,
            [
                TestRelatedTimestampableEntity::class => 'target',
            ],
        );

        $this->expectScheduledEntities(
            updates: [$entity],
        );

        $this->expectTargetToBeUpdated($middleTarget);
        $this->expectTargetToBeUpdated($lastTarget);

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    public function testDoesNotProcessSameRelatedEntityTwice(): void
    {
        $target = new TestRelatedTimestampableEntity();

        $entity = new TestRelatedTimestampableEntity(
            $target,
            [
                TestRelatedTimestampableEntity::class => 'target',
            ],
        );

        $this->expectScheduledEntities(
            insertions: [$entity],
            updates: [$entity],
        );

        $this->expectTargetToBeUpdated($target);

        $this->updater->onFlush(
            new OnFlushEventArgs($this->entityManager),
        );
    }

    /**
     * @param array<object> $insertions
     * @param array<object> $updates
     * @param array<object> $deletions
     */
    private function expectScheduledEntities(
        array $insertions = [],
        array $updates = [],
        array $deletions = [],
    ): void {
        $this->unitOfWork
            ->shouldReceive('getScheduledEntityInsertions')
            ->once()
            ->andReturn($insertions);

        $this->unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->once()
            ->andReturn($updates);

        $this->unitOfWork
            ->shouldReceive('getScheduledEntityDeletions')
            ->once()
            ->andReturn($deletions);
    }

    private function expectTargetToBeUpdated(
        TimestampableEntity $target,
    ): void {
        $this->unitOfWork
            ->shouldReceive('isScheduledForDelete')
            ->once()
            ->with($target)
            ->andReturnFalse();

        $this->unitOfWork
            ->shouldReceive('getEntityState')
            ->once()
            ->with($target)
            ->andReturn(UnitOfWork::STATE_MANAGED);

        $metadata = \Mockery::mock(ClassMetadata::class);

        $this->entityManager
            ->shouldReceive('getClassMetadata')
            ->once()
            ->with($target::class)
            ->andReturn($metadata);

        $this->unitOfWork
            ->shouldReceive('recomputeSingleEntityChangeSet')
            ->once()
            ->with($metadata, $target);
    }
}

final class TestTimestampableEntity extends TimestampableEntity
{
}

final class TestRelatedTimestampableEntity extends RelatedTimestampableEntity
{
    /** @phpstan-param array<class-string, string> $updatableRelations */
    public function __construct(
        private mixed $target = null,

        array $updatableRelations = [
            TestTimestampableEntity::class => 'target',
        ],
    ) {
        parent::__construct();

        $this->updatableRelations = $updatableRelations;
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }
}
