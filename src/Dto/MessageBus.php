<?php

namespace App\Dto;

use App\Application\Command\CommandInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

class MessageBus
{
    public function __construct(
        public CommandInterface $command,
        public ?AmqpStamp $stamp = null,
    ) {
    }
}
