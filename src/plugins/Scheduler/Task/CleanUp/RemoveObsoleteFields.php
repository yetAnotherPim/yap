<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Task\CleanUp;

use App\plugins\Scheduler\Task\AbstractTask;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('*/15 * * * *')]
class RemoveObsoleteFields extends AbstractTask
{
    public function run(): void
    {
        // TODO: implement
    }
}
