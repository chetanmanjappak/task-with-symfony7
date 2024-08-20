<?php
namespace App\Constants;

class Status
{
    public const UPLOAD_IN_QUEUE = 'UPLOAD-IN-QUEUE';
    public const UPLOAD_IN_PROGRESS = 'UPLOAD-IN-PROGRESS';
    public const UPLOAD_COMPLETED = 'UPLOAD-COMPLETED';
    public const SCANNING_IN_QUEUE = 'SCANNING-IN-QUEUE';
    public const SCANNING_IN_PROGRESS = 'SCANNING-IN-PROGRESS';
    public const SCANNING_COMPLETED = 'SCANNING-COMPLETED';
    public const FAILED = 'FAILED';
    public const SUCCESS = 'SUCCESS';
}
