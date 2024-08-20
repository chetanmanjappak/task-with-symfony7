<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for file upload functionality.
 */
class FileUploadDTO
{
    /**
     * @var array An array of files to be uploaded. At least one file is required.
     *
     * @Assert\NotNull(message="At least one file is required")
     * @Assert\Count(
     *     min=1,
     *     minMessage="You must upload at least one file"
     * )
     */
    public array $files = []; 

    /**
     * @var string|null The email address of the user. This field is required.
     *
     * @Assert\NotNull(message="Email is required")
     * @Assert\Email(message="Please enter a valid email address")
     */
    public ?string $email = null;

    /**
     * @var string The name of the batch. This field cannot be null.
     *
     * @Assert\NotNull(message="Batch name is required")
     */
    public ?string $batchName=null;

    /**
     * @var string|null The Slack ID or channel. This field is optional and can be null.
     *
     * @Assert\Regex(
     *     pattern="/^[@A-Za-z0-9._%+-]+$/",
     *     message="Please enter a valid Slack ID"
     * )
     */
    public ?string $slackChannel = null;
}
