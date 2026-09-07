<?php

declare(strict_types=1);


namespace App\Controller\Provider\SystemStatus\Message\Enum;

enum MessageType: string
{
    case Success = 'success';
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';
}
