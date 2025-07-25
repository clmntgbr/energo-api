<?php

namespace App\Service;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusService
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function dispatch(array $messages, ?AmqpStamp $stamp = null): void
    {
        foreach ($messages as $message) {
            $this->bus->dispatch(
                message: $message,
                stamps: $stamp ? [$stamp] : []
            );
        }
    }
}
