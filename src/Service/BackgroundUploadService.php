<?php

namespace App\Service;

use App\Entity\UploadBatch;
use App\Message\UploadMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service responsible for processing background uploads.
 */
class BackgroundUploadService
{
    private MessageBusInterface $bus;

    /**
     * @param MessageBusInterface $bus The message bus for dispatching messages.
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Dispatches an upload message to the message bus for background processing.
     *
     * @param UploadBatch $uploadBatch The upload batch to be processed.
     */
    public function processBackgroundUpload(UploadBatch $uploadBatch): void
    {
        $this->bus->dispatch(new UploadMessage($uploadBatch));
    }
}
