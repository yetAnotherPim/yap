<?php

declare(strict_types=1);

namespace App\Tests\unit\Setup\Environment;

use App\Setup\Database\Connection\Connection;
use App\Setup\Environment\EnvironmentFileWriter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Symfony\Component\Filesystem\Filesystem;

#[\PHPUnit\Framework\Attributes\CoversClass(EnvironmentFileWriter::class)]
class EnvironmentFileWriterTest extends MockeryTestCase
{
    /** @var Filesystem|MockInterface */
    private Filesystem $filesystem;

    private string $projectDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = \Mockery::mock(Filesystem::class);
        $this->projectDir = sys_get_temp_dir().'/yap-test';
    }

    public function testWriteCreatesEnvironmentFile(): void
    {
        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with($this->projectDir.'/.env.local')
            ->andReturnFalse();

        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(
                $this->projectDir.'/.env.local',
                \Mockery::on(function (string $contents): bool {
                    $this->assertStringContainsString(
                        'DATABASE_URL="postgresql://app:secret@127.0.0.1:5432/app"',
                        $contents,
                    );

                    $this->assertMatchesRegularExpression(
                        '/^APP_SECRET="[a-f0-9]{64}"$/m',
                        $contents,
                    );

                    $this->assertMatchesRegularExpression(
                        '/^JWT_PASSPHRASE="[a-f0-9]{64}"$/m',
                        $contents,
                    );

                    return true;
                }),
            );

        $writer = new EnvironmentFileWriter(
            $this->filesystem,
            $this->projectDir,
        );

        $writer->write($this->createConnection());
    }

    public function testWriteReplacesExistingDatabaseUrl(): void
    {
        $this->createExistingEnvironmentFile(
            "APP_ENV=dev\nDATABASE_URL=\"old-database-url\"\n",
        );

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with($this->projectDir.'/.env.local')
            ->andReturnTrue();

        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(
                $this->projectDir.'/.env.local',
                \Mockery::on(function (string $contents): bool {
                    $this->assertStringContainsString(
                        'DATABASE_URL="postgresql://app:secret@127.0.0.1:5432/app"',
                        $contents,
                    );

                    $this->assertStringNotContainsString(
                        'old-database-url',
                        $contents,
                    );

                    $this->assertStringContainsString('APP_ENV=dev', $contents);

                    return true;
                }),
            );

        $writer = new EnvironmentFileWriter(
            $this->filesystem,
            $this->projectDir,
        );

        $writer->write($this->createConnection());
    }

    public function testWriteEscapesPercentSignsInDatabaseUrl(): void
    {
        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->andReturnFalse();

        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(
                \Mockery::any(),
                \Mockery::on(function (string $contents): bool {
                    $this->assertStringContainsString(
                        'DATABASE_URL="postgresql://app:p%%40ss@127.0.0.1:5432/app"',
                        $contents,
                    );

                    return true;
                }),
            );

        $writer = new EnvironmentFileWriter(
            $this->filesystem,
            $this->projectDir,
        );

        $writer->write(
            $this->createConnection(
                'postgresql://app:p%40ss@127.0.0.1:5432/app',
            ),
        );
    }

    public function testWritePreservesExistingSecrets(): void
    {
        $this->createExistingEnvironmentFile(
            "APP_SECRET=\"existing-app-secret\"\nJWT_PASSPHRASE=\"existing-jwt-secret\"\n",
        );

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->andReturnTrue();

        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(
                \Mockery::any(),
                \Mockery::on(function (string $contents): bool {
                    $this->assertStringContainsString(
                        'APP_SECRET="existing-app-secret"',
                        $contents,
                    );

                    $this->assertStringContainsString(
                        'JWT_PASSPHRASE="existing-jwt-secret"',
                        $contents,
                    );

                    return true;
                }),
            );

        $writer = new EnvironmentFileWriter(
            $this->filesystem,
            $this->projectDir,
        );

        $writer->write($this->createConnection());
    }

    public function testWriteGeneratesSecretsForEmptyExistingValues(): void
    {
        $this->createExistingEnvironmentFile(
            "APP_SECRET=\nJWT_PASSPHRASE=\"\"\n",
        );

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->andReturnTrue();

        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(
                \Mockery::any(),
                \Mockery::on(function (string $contents): bool {
                    $this->assertMatchesRegularExpression(
                        '/^APP_SECRET="[a-f0-9]{64}"$/m',
                        $contents,
                    );

                    $this->assertMatchesRegularExpression(
                        '/^JWT_PASSPHRASE="[a-f0-9]{64}"$/m',
                        $contents,
                    );

                    return true;
                }),
            );

        $writer = new EnvironmentFileWriter(
            $this->filesystem,
            $this->projectDir,
        );

        $writer->write($this->createConnection());
    }

    private function createConnection(
        string $databaseUrl = 'postgresql://app:secret@127.0.0.1:5432/app',
    ): Connection {
        $connection = \Mockery::mock(Connection::class);

        $connection
            ->shouldReceive('getDatabaseUrl')
            ->once()
            ->andReturn($databaseUrl);

        return $connection;
    }

    private function createExistingEnvironmentFile(string $contents): void
    {
        if (!is_dir($this->projectDir)) {
            mkdir($this->projectDir, 0777, true);
        }

        file_put_contents(
            $this->projectDir.'/.env.local',
            $contents,
        );
    }
}
