<?php

declare(strict_types=1);

namespace App\Controller\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('system_status.checker')]
interface SystemCheckInterface
{
    /**
     * @return list<SystemStatusMessage>
     */
    public function check(): array;
}
