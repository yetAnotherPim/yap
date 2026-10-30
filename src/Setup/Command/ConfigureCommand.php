<?php

declare(strict_types=1);

namespace App\Setup\Command;

use App\Setup\Database\Connection\Connection;
use App\Setup\Database\Connection\Enum\DbType;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'yap:setup', description: 'Setup YAP')]
class ConfigureCommand extends Command
{
    private readonly ValidatorInterface $validator;

    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Yet Another PIM - Setup');
        $io->section('Database configuration');

        $connection = $this->configureConnection($io);

        $io->success(\sprintf(
            'Database connection successful. Version: %s',
            $connection->getVersion(),
        ));

        $this->writeEnvironmentFile($connection);

        $io->success('Database configuration saved to .env.local.');

        return Command::SUCCESS;
    }

    private function configureConnection(SymfonyStyle $io): Connection
    {
        while (true) {
            $connection = new Connection();

            $type = $io->choice(
                'Select your database type',
                array_map(
                    static fn (DbType $type): string => $type->value,
                    DbType::cases(),
                ),
                DbType::PostgreSQL->value,
            );

            $type = DbType::from($type);

            $connection
                ->setType($type)
                ->setHost($io->ask('Database host', '127.0.0.1'))
                ->setPort($io->ask('Database port', (string) $type->getDefaultPort()))
                ->setDbname($io->ask('Database name', 'app'))
                ->setUser($io->ask('Database user', 'app'))
                ->setPassword($io->askHidden('Database password') ?? '');

            $violations = $this->validator->validate($connection);

            if (\count($violations) > 0) {
                $io->error((string) $violations);

                continue;
            }

            if (!$this->testConnection($connection, $io)) {
                continue;
            }

            return $connection;
        }
    }

    private function testConnection(Connection $connection, SymfonyStyle $io): bool
    {
        $dbalConnection = DriverManager::getConnection([
            'driver' => $connection->getType()->getDriver(),
            'host' => $connection->getHost(),
            'port' => $connection->getPort(),
            'dbname' => $connection->getDbname(),
            'user' => $connection->getUser(),
            'password' => $connection->getPassword(),
            'charset' => $connection->getType()->getDefaultCharset(),
        ]);

        try {
            $dbalConnection->executeQuery('SELECT 1');

            $connection->setVersion($dbalConnection->getServerVersion());

            return true;
        } catch (\Doctrine\DBAL\Exception $exception) {
            $io->error([
                'Could not connect to the database.',
                $exception->getMessage(),
            ]);

            return false;
        } finally {
            $dbalConnection->close();
        }
    }

    private function writeEnvironmentFile(Connection $connection): void
    {
        $envFile = $this->projectDir.'/.env.local';

        $contents = $this->filesystem->exists($envFile)
            ? file_get_contents($envFile)
            : '';

        if (false === $contents) {
            throw new \RuntimeException('Could not read .env.local.');
        }

        $line = \sprintf(
            'DATABASE_URL="%s"',
            str_replace('%', '%%', $connection->getDatabaseUrl()),
        );

        $pattern = '/^[ \t]*DATABASE_URL[ \t]*=.*$/m';

        if (preg_match($pattern, $contents)) {
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
