<?php

declare(strict_types=1);

namespace App\PluginManager\Event\WorkflowEvent\Exception;

use App\PluginManager\Event\Enum\Action;

class PluginWorkflowException extends \Exception
{
    public function __construct(private readonly Action $action, private readonly string $pluginName, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }
}
