<?php

namespace App\Message;

/**
 * Message class for processing scan tasks.
 */
class ProcessScanMessage
{
    private string $scanId;

    /**
     * @param string $scanId The unique identifier for the scan.
     */
    public function __construct(string $scanId)
    {
        $this->scanId = $scanId;
    }

    /**
     * Gets the scan ID.
     *
     * @return string The unique identifier for the scan.
     */
    public function getScanId(): string
    {
        return $this->scanId;
    }
}
