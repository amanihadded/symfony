<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notification')]
class NotificationController extends AbstractController
{
    #[Route('/', name: 'notification_index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        $notifications = $notificationRepository->findRecent(50);
        $unreadCount = $notificationRepository->countUnread();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/recent', name: 'notification_recent', methods: ['GET'])]
    public function recent(NotificationRepository $notificationRepository): JsonResponse
    {
        $notifications = $notificationRepository->findRecent(5);
        $unreadCount = $notificationRepository->countUnread();

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'icon' => $notification->getIcon(),
                'isRead' => $notification->isRead(),
                'timeAgo' => $notification->getTimeAgo(),
                'link' => $notification->getLink(),
            ];
        }

        return new JsonResponse([
            'notifications' => $data,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/mark-all-read', name: 'notification_mark_all_read', methods: ['POST'])]
    public function markAllRead(NotificationRepository $notificationRepository): JsonResponse
    {
        $notificationRepository->markAllAsRead();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/read', name: 'notification_mark_read', methods: ['POST'])]
    public function markRead(int $id, NotificationRepository $notificationRepository, EntityManagerInterface $em): JsonResponse
    {
        $notification = $notificationRepository->find($id);
        if ($notification) {
            $notification->setIsRead(true);
            $em->flush();
        }

        return new JsonResponse(['success' => true]);
    }
}
