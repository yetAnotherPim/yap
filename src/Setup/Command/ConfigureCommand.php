<?php

declare(strict_types=1);

namespace App\Setup\Command;

use App\Setup\Database\Connection\Connection;
use App\Setup\Database\Connection\Enum\DbType;
use App\Setup\Environment\EnvironmentFileWriter;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'yap:setup',
    description: 'Configure the database and initialize the application.',
)]
class ConfigureCommand extends Command
{
    private readonly ValidatorInterface $validator;

    public function __construct(
        private readonly EnvironmentFileWriter $environmentFileWriter,
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(<<<'HELP'
        This command guides you through the initial setup of Yet Another PIM (YAP).

        It performs the following steps:

          1. Configure the database connection:
             - Select the database type (PostgreSQL or MySQL).
             - Enter the connection details.
             - Validate the configuration and test the database connection.

          2. Configure application environment variables:
             - Write the database connection URL to .env.local.
             - Generate APP_SECRET if it is missing or empty.
             - Generate JWT_PASSPHRASE if it is missing or empty.
             - Preserve existing non-empty secret values.

        The configuration is stored in the local .env.local file.
        The file is not intended to be committed to version control.

        After completing the setup, further installation steps may be required,
        such as generating the JWT key pair and compiling production assets.
        HELP);
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

        $this->environmentFileWriter->write($connection);

        $io->success('Database configuration saved to .env.local.');

        $this->displayNextSteps($io);

        return Command::SUCCESS;
    }

    private function configureConnection(SymfonyStyle $io): Connection
    {
        while (true) {
            $connection = new Connection();

            /** @var string $type */
            $type = $io->choice(
                'Select your database type',
                array_map(
                    static fn (DbType $type): string => $type->value,
                    DbType::cases(),
                ),
                DbType::PostgreSQL->value,
            );

            $type = DbType::from($type);

            /** @var string $host */
            $host = $io->ask('Database host', '127.0.0.1');

            /** @var string $port */
            $port = $io->ask('Database port', (string) $type->getDefaultPort());

            /** @var string $dbname */
            $dbname = $io->ask('Database name', 'app');

            /** @var string $user */
            $user = $io->ask('Database user', 'app');

            /** @var ?string $password */
            $password = $io->askHidden('Database password');

            $connection
                ->setType($type)
                ->setHost($host)
                ->setPort($port)
                ->setDbname($dbname)
                ->setUser($user)
                ->setPassword($password ?? '');

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

    private function displayNextSteps(SymfonyStyle $io): void
    {
        $io->section('Next steps');

        $io->text(
            'Your database connection and application secrets have been configured.'
        );

        $io->text('Complete the following steps to finish the installation:');

        $io->listing([
            'Create the database: <comment>php bin/console doctrine:database:create --if-not-exists</comment>',
            'Run database migrations: <comment>php bin/console doctrine:migrations:migrate --allow-no-migration</comment>',
            'Generate the JWT key pair: <comment>php bin/console lexik:jwt:generate-keypair --skip-if-exists</comment>',
            'Create the first user: <comment>php bin/console yap:user:create</comment>',
        ]);

        $io->section('Production environment');

        $io->text('Additionally, execute the following commands for production deployments:');

        $io->listing([
            'Compile environment variables: <comment>composer dump-env prod</comment>',
            'Compile assets: <comment>bin/console asset-map:compile</comment>',
        ]);
    }
}
