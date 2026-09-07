<?php

namespace App\Controller\Provider\SystemStatus;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Translation\TranslatableMessage;

#[AutoconfigureTag('system_status.checker')]
interface SystemCheckInterface
{
    /**
     * @return list<TranslatableMessage>
     */
    public function check(): array;
}
