<?php

namespace App\Twig;

use App\Repository\NotificationRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NotificationExtension extends AbstractExtension implements GlobalsInterface
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getGlobals(): array
    {
        return [
            'unread_notifications_count' => $this->notificationRepository->countUnread(),
            'recent_notifications' => $this->notificationRepository->findRecent(5),
        ];
    }
}
