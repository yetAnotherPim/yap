<?php

declare(strict_types=1);

namespace App\Setup\Environment;

use App\Setup\Database\Connection\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class EnvironmentFileWriter
{
    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    public function write(Connection $connection): void
    {
        $envFile = $this->projectDir.'/.env.local';

        $contents = $this->filesystem->exists($envFile)
            ? file_get_contents($envFile)
            : '';

        if (false === $contents) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Could not read .env.local.');
            // @codeCoverageIgnoreEnd
        }

        $line = \sprintf(
            'DATABASE_URL="%s"',
            str_replace('%', '%%', $connection->getDatabaseUrl()),
        );

        $pattern = '/^[ \t]*DATABASE_URL[ \t]*=.*$/m';

        if (preg_match($pattern, $contents)) {
            /** @phpstan-var string $contents */
            $contents = preg_replace_callback(
                $pattern,
                static fn (): string => $line,
                $contents,
            );
        } else {
            $contents = rtrim($contents)."\n".$line."\n";
        }

        $this->writeSecret($contents, 'APP_SECRET');
        $this->writeSecret($contents, 'JWT_PASSPHRASE');

        $this->filesystem->dumpFile($envFile, $contents);
    }

    private function writeSecret(string &$contents, string $name): void
    {
        $pattern = '/^[ \t]*'.preg_quote($name, '/').'[ \t]*=[ \t]*(.*?)\s*$/m';

        if (preg_match($pattern, $contents, $matches)) {
            if ('' !== trim($matches[1], " \t\"'")) {
                return;
            }

            $line = \sprintf(
                '%s="%s"',
                $name,
                bin2hex(random_bytes(32)),
            );

            $updatedContents = preg_replace(
                $pattern,
                $line,
                $contents,
                1,
            );

            // @codeCoverageIgnoreStart
            if (null === $updatedContents) {
                throw new \RuntimeException(\sprintf('Could not update %s.', $name));
            }
            // @codeCoverageIgnoreEnd

            $contents = $updatedContents;

            return;
        }

        $line = \sprintf(
            '%s="%s"',
            $name,
            bin2hex(random_bytes(32)),
        );

        $contents = rtrim($contents)."\n".$line."\n";
    }
}
