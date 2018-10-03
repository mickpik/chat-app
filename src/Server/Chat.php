<?php

namespace App\Server;

use App\Entity\User;
use App\Enum\CommandConfig;
use App\Model\Connection;
use App\Repository\UserRepository;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface
{
    /** @var Connection[] */
    private $connections = [];

    /** @var UserRepository */
    private $userRepository;

    /**
     * Chat constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritdoc
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->connections[] = new Connection($conn);
        $conn->send(sprintf(
            'Welcome, %d user(s) connected... Please enter your nickname to start chatting',
            count($this->connections)
        ));
    }

    /**
     * @inheritdoc
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->closeConnection($conn);
    }

    /**
     * @inheritdoc
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->send(sprintf('Error: %s', $e->getMessage()));
        $this->closeConnection($conn);
    }

    /**
     * @inheritdoc
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $connection = $this->getConnectionFromList($from);
        if ($connection->getUser() === null) {
            $this->handleUsername($msg, $connection);
            return;
        }

        if (substr($msg, 0, 1) === '/') {
            $this->handleCommand($msg, $connection);
            return;
        }

        foreach ($this->connections as $openConnection) {
            $openConnection->getConnection()->send(
                sprintf('%s> %s', strtoupper($connection->getUser()->getUsername()), $msg)
            );
        }
    }

    /**
     * @param ConnectionInterface $connection
     */
    private function closeConnection(ConnectionInterface $connection): void
    {
        foreach ($this->connections as $key => $openConnection) {
            if ($openConnection->getConnection() === $connection) {
                unset($this->connections[$key]);
                break;
            }
        }

        $connection->send('Closing connection, bye...');
        $connection->close();
    }

    /**
     * @param string $username
     * @param Connection $connection
     * @return Connection
     */
    private function handleUsername(string $username, Connection $connection): Connection
    {
        try {
            $user = $this->userRepository->findByUsername($username);
            if ($user !== null) {
                $connection->getConnection()->send(
                    sprintf('Username %s is in use, please chose another one', $username)
                );
            } else {
                // A connection will have a user if this method is call trough /nick. If not; create a new one
                $user = $connection->getUser();
                if ($user === null) {
                    $user = new User();
                }
                $user->setUsername($username);
                $this->userRepository->save($user);

                $connection->getConnection()->send('Username successfully created, you can start chatting');
                $connection->setUser($user);
            }
        } catch (\Exception $e) {
            $connection->getConnection()->send(sprintf('Something went wrong: ', $e->getMessage()));
        }

        return $connection;
    }

    /**
     * @param string $command
     * @param Connection $connection
     */
    private function handleCommand(string $command, Connection $connection)
    {
        switch ($command) {
            case $this->checkCommand($command, CommandConfig::QUIT):
                $this->closeConnection($connection->getConnection());
                break;
            case $this->checkCommand($command, CommandConfig::NICKNAME):
                $newNick = trim(substr($command, strlen(CommandConfig::NICKNAME)));
                $this->handleUsername($newNick, $connection);
                break;
            default:
                $connection->getConnection()->send('Unrecognized command');
        }
    }

    /**
     * @param string $input
     * @param string $command
     * @return bool
     */
    private function checkCommand(string $input, string $command)
    {
        return substr($input, 0, strlen($command)) === $command;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Connection|null
     */
    private function getConnectionFromList(ConnectionInterface $connection): ?Connection
    {
        foreach ($this->connections as $openConnection) {
            if ($openConnection->getConnection() === $connection) {
                return $openConnection;
            }
        }

        return null;
    }
}
