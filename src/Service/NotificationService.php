<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(string $message, string $type = 'info', ?string $icon = null, ?string $link = null): Notification
    {
        $notification = new Notification();
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setIcon($icon ?? $this->getDefaultIcon($type));
        $notification->setLink($link);

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function notifyCategoryCreated(string $categoryName): Notification
    {
        return $this->create(
            'Nouvelle catégorie "' . $categoryName . '" créée',
            'category',
            'bi-tags-fill'
        );
    }

    public function notifyProductCreated(string $productName): Notification
    {
        return $this->create(
            'Nouveau produit "' . $productName . '" ajouté',
            'product',
            'bi-box-fill'
        );
    }

    public function notifyFournisseurCreated(string $fournisseurName): Notification
    {
        return $this->create(
            'Nouveau fournisseur "' . $fournisseurName . '" enregistré',
            'fournisseur',
            'bi-truck'
        );
    }

    private function getDefaultIcon(string $type): string
    {
        $icons = [
            'category' => 'bi-tags-fill',
            'product' => 'bi-box-fill',
            'fournisseur' => 'bi-truck',
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'danger' => 'bi-x-circle-fill',
        ];

        return $icons[$type] ?? 'bi-bell-fill';
    }
}
