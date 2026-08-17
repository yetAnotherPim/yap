<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Resolver;

use App\Form\Product\Fields\Resolver\Enum\FileType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

class FileResolver
{
    private const string FILE_PATH = 'public/uploads/files/';
    private const string IMAGE_PATH = 'public/uploads/images/';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function resolve(?string $filePath, FileType $fileType): ?File
    {
        if (null === $filePath) {
            return null;
        }

        $file = new File($this->getBasePath($fileType).$filePath, false);

        if (false === $file->isFile()) {
            return null;
        }

        return $file;
    }

    public function getUploadDirectory(FileType $fileType): string
    {
        if (FileType::IMAGE === $fileType) {
            return self::IMAGE_PATH;
        }

        return self::FILE_PATH;
    }

    private function getBasePath(FileType $fileType): string
    {
        return $this->projectDir.'/'.$this->getUploadDirectory($fileType);
    }
}
