<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait\Api;

use App\Entity\User\Enum\Roles;
use App\Tests\TestData\Factory\User\UserFactory;
use App\Tests\TestData\Story\MultipleLocalesStory;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

trait AuthTrait
{
    protected string $authToken;

    protected function auth(): void
    {
        $this->createRequiredTestData();

        try {
            $response = $this->client->request(
                'POST',
                '/api/auth',
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => '{
                                    "username":"api",
                                    "password":"admin"
                                }',
                ]
            );
        } catch (TransportExceptionInterface) {
            $this->fail('Could not connect to the API');
        }

        try {
            $json = $response->toArray();
        } catch (ExceptionInterface) {
            $this->fail('Could not decode the response');
        }

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
        $this->assertIsString($json['token']);

        $this->authToken = $json['token'];
    }

    private function createRequiredTestData(): void
    {
        UserFactory::createOne(
            [
                'username' => 'api',
                'firstName' => 'first name',
                'lastName' => 'last name',
                'email' => 'email@example.com',
                'role' => Roles::ROLE_API_USER,
                'password' => '$2y$13$S7Hz4gW2KuX4GpA8c4Ixz.1CK8VKkHt19AEg3w9fSZjr8DRF57DOa',
            ]
        );

        MultipleLocalesStory::load();
    }
}
