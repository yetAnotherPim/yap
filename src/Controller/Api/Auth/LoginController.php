<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractController
{
    public function login(): Response
    {
        throw new \LogicException('Credentials required');
    }
}
