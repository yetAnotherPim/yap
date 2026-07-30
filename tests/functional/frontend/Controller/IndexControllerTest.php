<?php

declare(strict_types=1);

namespace App\Tests\functional\frontend\Controller;

use App\Controller\IndexController;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Repository\LocaleRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(IndexController::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(LocaleListener::class)]
final class IndexControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/admin');
    }
}
