<?php

namespace App\Controller;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\NoteType\CommonNote;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends AbstractController
{
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function leadUpdate(Request $request)
    {
        $apiClient = new AmoCRMApiClient();
        $apiClient->setAccessToken(new LongLivedAccessToken(''))
            ->setAccountBaseDomain('amocrm.ru');

        $leadId = $_POST['leads']['update'][0]['id'];
        $updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $updatedAtFormat = $updatedAt->setTimestamp($_POST['leads']['update'][0]['updated_at'])->format('Y-m-d H:i:s');

        $notesCollection = new NotesCollection();
        $commonNote = new CommonNote();
        $commonNote->setEntityId($leadId)
            ->setText('Сделка изменена '. $updatedAtFormat);
        $notesCollection->add($commonNote);

        try {
            $apiClient->notes(EntityTypesInterface::LEADS)->add($notesCollection);
        } catch (AmoCRMApiException $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'code' => $e->getCode(),
                    'description' => $e->getDescription(),
                    'requestInfo' => $e->getLastRequestInfo(),
                ]
            );
            die;
        }

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
