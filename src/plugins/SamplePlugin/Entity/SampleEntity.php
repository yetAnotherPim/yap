<?php

declare(strict_types=1);

namespace App\plugins\SamplePlugin\Entity;

use App\plugins\SamplePlugin\Repository\SampleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SampleRepository::class)]
class SampleEntity
{

}
