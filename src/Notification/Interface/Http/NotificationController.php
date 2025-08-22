<?php

namespace App\Notification\Interface\Http;

use App\Notification\Application\Service\NotificationSender;
use App\Notification\Domain\Model\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationSender $sender,
    ) {
    }

    #[Route('/notifications', name: 'notification', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $notification = new Notification(
            $data['user_id'],
            $data['message'],
            $data['channels'],
            $data['phone']
        );

        $this->sender->send($notification);

        return new JsonResponse(['status' => 'success', 'notification' => $notification]);
    }

    #[Route('/api', name: 'default', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse(['status' => 'test']);
    }
}
