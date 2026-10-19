<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Resolver;

use App\Form\Product\Fields\Resolver\Enum\FileType;
use App\Form\Product\Fields\Resolver\FileResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

#[CoversClass(FileResolver::class)]
class FileResolverTest extends TestCase
{
    private string $projectDir;

    private FileResolver $resolver;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/file-resolver-'.uniqid('', true);

        mkdir($this->projectDir.'/public/uploads/files', 0777, true);
        mkdir($this->projectDir.'/public/uploads/images', 0777, true);

        $this->resolver = new FileResolver($this->projectDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDir);
    }

    public function testResolveReturnsNullForNullFilePath(): void
    {
        $result = $this->resolver->resolve(null, FileType::FILE);

        $this->assertNull($result);
    }

    public function testResolveReturnsFileForExistingFile(): void
    {
        $filePath = 'example.txt';
        $absolutePath = $this->projectDir.'/public/uploads/files/'.$filePath;

        file_put_contents($absolutePath, 'Test');

        $result = $this->resolver->resolve($filePath, FileType::FILE);

        $this->assertInstanceOf(File::class, $result);
        $this->assertSame($absolutePath, $result->getPathname());
    }

    public function testResolveReturnsImageForExistingImage(): void
    {
        $filePath = 'example.jpg';
        $absolutePath = $this->projectDir.'/public/uploads/images/'.$filePath;

        file_put_contents($absolutePath, 'Test');

        $result = $this->resolver->resolve($filePath, FileType::IMAGE);

        $this->assertInstanceOf(File::class, $result);
        $this->assertSame($absolutePath, $result->getPathname());
    }

    public function testResolveReturnsNullForNonExistingFile(): void
    {
        $result = $this->resolver->resolve(
            'does-not-exist.txt',
            FileType::FILE,
        );

        $this->assertNull($result);
    }

    public function testResolveReturnsNullForNonExistingImage(): void
    {
        $result = $this->resolver->resolve(
            'does-not-exist.jpg',
            FileType::IMAGE,
        );

        $this->assertNull($result);
    }

    public function testGetUploadDirectoryReturnsFileDirectory(): void
    {
        $this->assertSame(
            'public/uploads/files/',
            $this->resolver->getUploadDirectory(FileType::FILE),
        );
    }

    public function testGetUploadDirectoryReturnsImageDirectory(): void
    {
        $this->assertSame(
            'public/uploads/images/',
            $this->resolver->getUploadDirectory(FileType::IMAGE),
        );
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
