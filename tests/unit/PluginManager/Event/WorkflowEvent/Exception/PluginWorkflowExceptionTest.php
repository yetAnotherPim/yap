<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\WorkflowEvent\Exception;

use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PluginWorkflowException::class)]
class PluginWorkflowExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new PluginWorkflowException(
            Action::ACTIVATE,
            'SamplePlugin',
            'Message',
            1337,
            new \RuntimeException('Error')
        );

        $this->assertEquals(Action::ACTIVATE, $exception->getAction());
        $this->assertEquals('SamplePlugin', $exception->getPluginName());
        $this->assertEquals('Message', $exception->getMessage());
        $this->assertEquals(1337, $exception->getCode());
        $this->assertInstanceOf(\RuntimeException::class, $exception->getPrevious());
        $this->assertEquals('Error', $exception->getPrevious()->getMessage());
    }
}
