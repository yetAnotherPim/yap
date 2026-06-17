<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use App\Entity\Abstract\RelatedTimestampableEntity;

trait UpdateRelatedTrait
{
    public function testUpdateRelated(): void
    {
        $targetClass = $this->getTargetClass();
        /** @phpstan-var RelatedTimestampableEntity $target */
        $target = new $targetClass();

        $class = new \ReflectionClass($target);
        $property = $class->getProperty('updatableRelations');

        /** @phpstan-var array<class-string, string> $relations */
        $relations = $property->getValue($target);

        foreach ($relations as $type => $relation) {
            $relatedEntities[$type] = \Mockery::mock($type);

            $setter = 'set'.ucfirst($relation);
            $target->$setter($relatedEntities[$type]);

            $relatedEntities[$type]
                ->shouldReceive('setUpdatedAt')
                ->once();
        }

        $target->updateRelated();
    }

    public function testUpdateRelatedWithInvalidTypeSpecification(): void
    {
        $targetClass = $this->getTargetClass();
        /** @phpstan-var RelatedTimestampableEntity $target */
        $target = new $targetClass();

        $class = new \ReflectionClass($target);
        $property = $class->getProperty('updatableRelations');

        /** @phpstan-var array<class-string, string> $relations */
        $relations = $property->getValue($target);

        /** @phpstan-var string $relation */
        $relation = array_first($relations);
        /** @phpstan-var class-string $relation */
        $type = array_key_first($relations);

        $property->setValue($target, ['Something' => $relation]);

        $relatedEntity = \Mockery::mock($type);

        $setter = 'set'.ucfirst($relation);
        $target->$setter($relatedEntity);

        $relatedEntity
            ->shouldReceive('setUpdatedAt')
            ->never();

        $target->updateRelated();
    }

    abstract protected function getTargetClass(): string;
}
