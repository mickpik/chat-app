<?php

namespace App\Command;

use App\Server\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    /** @var Chat */
    private $chat;

    /**
     * ServerCommand constructor.
     * @param Chat $chat
     */
    public function __construct(Chat $chat)
    {
        parent::__construct();
        $this->chat = $chat;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('chat:server:start');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Starting server....');

        $ws = new WsServer($this->chat);
        $server = IoServer::factory(new HttpServer($ws), 8080);

        $server->run();
    }
}
