<?php

namespace App\Service;

use App\Application\Command\CommandInterface;
use App\Dto\MessageBus;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusService
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @param MessageBus[] $messages
     */
    public function dispatch(array $messages): void
    {
        array_map(
            fn($message) => $this->bus->dispatch(
                message: $message->command,
                stamps: $message->stamp ? [$message->stamp] : []
            ),
            $messages
        );
    }
}
