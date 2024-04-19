<?php

namespace App\Controller;

use App\Service\NoteService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends AbstractController
{
    private $noteService;
    private $logger;

    public function __construct(
        NoteService $noteService,
        LoggerInterface $logger
    ) {
        $this->noteService = $noteService;
        $this->logger = $logger;
    }

    public function leadUpdate(Request $request)
    {
        $leadId = $_POST['leads']['update'][0]['id'];
        $updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $updatedAtFormat = $updatedAt->setTimestamp($_POST['leads']['update'][0]['updated_at'])->format('Y-m-d H:i:s');
        $this->noteService->addCommonNote('leads', $leadId, 'Сделка изменена '. $updatedAtFormat);

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
