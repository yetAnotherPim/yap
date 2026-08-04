<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait\Frontend;

use App\Entity\User\Enum\Roles;
use App\Tests\TestData\Factory\User\UserFactory;
use App\Tests\TestData\Story\MultipleLocalesStory;

trait LoginTrait
{
    private bool $hasCreatedTestData = false;

    protected function login(Roles $role = Roles::ROLE_SUPER_ADMIN): void
    {
        $this->createRequiredTestData();

        $this->client->restart();

        $this->client->request('GET', '/admin/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => $role->name,
            '_password' => 'password',
        ]);

        $this->assertResponseRedirects('/admin');
        $this->client->followRedirect();

        $this->assertSelectorNotExists('.alert-danger');
    }

    private function createRequiredTestData(): void
    {
        if (true === $this->hasCreatedTestData) {
            return;
        }

        foreach (Roles::cases() as $role) {
            UserFactory::createOne(
                [
                    'username' => $role->name,
                    'firstName' => 'first name',
                    'lastName' => 'last name',
                    'email' => $role->value.'@example.com',
                    'role' => $role,
                    'password' => '$2y$13$YJfPafOvNh8WcUIjwBbQoOaIBKocblDa9TTZCUIEcFlBpRB7ETTxy',
                ]
            );
        }

        MultipleLocalesStory::load();

        $this->hasCreatedTestData = true;
    }
}
