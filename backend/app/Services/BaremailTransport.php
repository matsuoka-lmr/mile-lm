<?php

namespace App\Services;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BaremailTransport extends AbstractTransport
{
    /**
     * Create a new Baremail transport instance.
     */
    public function __construct(
        protected Baremail $client,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $this->client->send($email);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'baremail';
    }
}
