<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait\Frontend;

use App\Tests\TestData\Factory\User\UserFactory;
use App\Tests\TestData\Story\MultipleLocalesStory;

trait LoginTrait
{
    protected function login(): void
    {
        $this->createRequiredTestData();

        $this->client->request('GET', '/admin/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'admin',
            '_password' => 'admin',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
    }

    private function createRequiredTestData(): void
    {
        UserFactory::createOne(
            [
                'username' => 'admin',
                'firstName' => 'first name',
                'lastName' => 'last name',
                'email' => 'email@example.com',
                'password' => '$2y$13$S7Hz4gW2KuX4GpA8c4Ixz.1CK8VKkHt19AEg3w9fSZjr8DRF57DOa',
            ]
        );

        MultipleLocalesStory::load();
    }
}
