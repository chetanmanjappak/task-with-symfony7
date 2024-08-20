<?php

namespace App\Service;

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use GuzzleHttp\Client;
use App\Entity\User;
use App\Entity\NotificationLogs;
use Doctrine\ORM\EntityManagerInterface;
use App\Constants\Status;
use App\Constants\General;

/**
 * Service class for sending notifications via email and Slack.
 */
class NotificationService
{
    private NotifierInterface $notifier;
    private Client $guzzleHttp;
    private EntityManagerInterface $entityManager;

    /**
     * @param NotifierInterface $notifier The notifier service for sending notifications.
     * @param Client $guzzleHttp The HTTP client for sending Slack messages.
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
     */
    public function __construct(NotifierInterface $notifier, Client $guzzleHttp, EntityManagerInterface $entityManager)
    {
        $this->notifier = $notifier;
        $this->guzzleHttp = $guzzleHttp;
        $this->entityManager = $entityManager;
    }

    /**
     * Sends a notification to a user via email and Slack.
     *
     * @param User $user The user to receive the notification.
     * @param string $type The type of the notification.
     * @param string $message The message content of the notification.
     *
     * @return void
     */
    public function sendNotification(User $user, string $type, array $messageBindParams): void
    {
        $email = $user->getEmail();
        $slackChannel = $user->getSlackChannel();
        $messageContent = self::generateMessage($type,$messageBindParams);
        // Create and send email notification
        $emailNotification = (new Notification($messageContent['subject'], ['email']))
            ->importance($messageContent['priority'])
            ->content($messageContent['message']);
        $emailRecipient = new Recipient($email);
        $this->notifier->send($emailNotification, $emailRecipient);
        
        $notification = [
            'cat' => 'mail',
            'type' => $type,
            'message' => $messageContent['message'],
            'status' => Status::SUCCESS
        ];
        $this->saveNotification($user, $notification);

        // Send Slack notification if a channel is provided
        if ($slackChannel) {
            $slackNotification = (new Notification($messageContent['message'], ['chat']))
            ->importance($messageContent['priority']);
            $slackRecipient = new Recipient($slackChannel);
            $this->notifier->send($slackNotification, $slackRecipient);
            $this->saveNotification($user, $notification);
        }
    }

    /**
     * Generates a notification message based on the type.
     *
     * @param string $notificationType The type of notification (e.g., 'SCAN_COMPLETED', 'UPLOAD_STARTED', 'UPLOAD_FAILED').
     * @param array $parameters The parameters required to generate the message.
     * 
     * @return array The generated notification message and subject.
     */
    public static function generateMessage(string $notificationType, array $parameters): array
    {
        $prefix = 'Simple Rule Engine: ';
        switch ($notificationType) {
            case General::NOTIFICATION_FOUND_VULNERABILITIES:
                return ['message'=>sprintf(
                    "Scan completed for batch '%s': The file '%s' contains %d identified vulnerabilities.",
                    $parameters[0], // $batchName
                    $parameters[1], // $fileName
                    $parameters[2]  // $vulnerabilitiesFound
                ),'subject'=>$prefix.'Scanning Result for '.$parameters[0],'priority'=>Notification::IMPORTANCE_HIGH];
            case General::NOTIFICATION_UPLOAD_IN_PROGRESS:
                return ['message'=>sprintf(
                    "The upload of files from batch '%s' has started.",
                    $parameters[0] // $batchName
                ),'subject'=>$prefix.'Upload In Progress for '.$parameters[0],'priority'=>Notification::IMPORTANCE_LOW];
            case General::NOTIFICATION_UPLOAD_FAILED:
                return ['message'=>sprintf(
                    "File upload failed for batch '%s': The file '%s' encountered the following error: %s.",
                    $parameters[0], // $batchName
                    $parameters[1], // $fileName
                    $parameters[2]  // $errorMessage
                ),'subject'=>$prefix.'File Upload Failed','priority'=>Notification::IMPORTANCE_HIGH];
            default:
                return ['message'=>'NA', 'subject'=>$prefix,'priority'=>Notification::IMPORTANCE_LOW];
        }
    }

    /**
     * Saves a notification log to the database.
     *
     * @param User $user The user associated with the notification.
     * @param array $data The notification data to save.
     *
     * @return void
     */
    public function saveNotification(User $user, array $data): void
    {
        $notification = new NotificationLogs();
        $notification->setUser($user);
        $notification->setCategory($data['cat']);
        $notification->setNotificationType($data['type']);
        $notification->setMessage($data['message']);
        $notification->setStatus($data['status']);
        $notification->setSentAt();
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
