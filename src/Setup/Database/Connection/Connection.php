<?php

declare(strict_types=1);

namespace App\Setup\Database\Connection;

use App\Setup\Database\Connection\Enum\DbType;
use Symfony\Component\Validator\Constraints as Assert;

class Connection
{
    private DbType $type;

    #[Assert\NotBlank]
    private string $host = '';

    #[Assert\Positive]
    private int $port = 0;

    #[Assert\NotBlank]
    private string $dbname = '';

    #[Assert\NotBlank]
    private string $user = '';

    #[Assert\NotBlank]
    private string $password = '';

    private string $version = '';

    public function getType(): DbType
    {
        return $this->type;
    }

    public function setType(DbType $type): Connection
    {
        $this->type = $type;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): Connection
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int|string $port): Connection
    {
        $this->port = \intval($port);

        return $this;
    }

    public function getDbname(): string
    {
        return $this->dbname;
    }

    public function setDbname(string $dbname): Connection
    {
        $this->dbname = $dbname;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): Connection
    {
        $this->user = $user;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Connection
    {
        $this->password = $password;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): Connection
    {
        $this->version = $version;

        return $this;
    }

    public function getDatabaseUrl(): string
    {
        return \sprintf(
            '%s://%s:%s@%s:%d/%s?serverVersion=%s&charset=%s',
            $this->type->value,
            rawurlencode($this->user),
            rawurlencode($this->password),
            $this->host,
            $this->port,
            rawurlencode($this->dbname),
            rawurlencode($this->version),
            $this->type->getDefaultCharset(),
        );
    }
}
