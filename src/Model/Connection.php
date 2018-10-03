<?php

namespace App\Model;

use App\Entity\User;
use Ratchet\ConnectionInterface;

class Connection
{
    /** @var ConnectionInterface */
    private $connection;

    /** @var User|null */
    private $user;

    /**
     * Connection constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Connection
     */
    public function setConnection(ConnectionInterface $connection): Connection
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return Connection
     */
    public function setUser(?User $user): Connection
    {
        $this->user = $user;
        return $this;
    }
}
