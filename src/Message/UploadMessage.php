<?php

namespace App\Message;

use App\Entity\UploadBatch;

/**
 * Represents a message containing an upload batch for background processing.
 */
class UploadMessage
{
    private UploadBatch $uploadBatch;

    /**
     * @param UploadBatch $uploadBatch The upload batch associated with this message.
     */
    public function __construct(UploadBatch $uploadBatch)
    {
        $this->uploadBatch = $uploadBatch;
    }

    /**
     * Gets the upload batch associated with this message.
     *
     * @return UploadBatch The upload batch.
     */
    public function getBatch(): UploadBatch
    {
        return $this->uploadBatch;
    }
}
