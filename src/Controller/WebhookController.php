<?php

namespace App\Controller;

use App\Service\DataService;
use App\Service\NoteService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends AbstractController
{
    private $dataService;
    private $noteService;
    private $userService;
    private $logger;

    public function __construct(
        DataService $dataService,
        NoteService $noteService,
        UserService $userService,
        LoggerInterface $logger
    ) {
        $this->dataService = $dataService;
        $this->noteService = $noteService;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function leadCreate(Request $request)
    {
        $lead = $_POST['leads']['add'][0];
        $createdAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $createdAtFormat = $createdAt->setTimestamp($lead['created_at'])->format('Y-m-d H:i:s');
        $responsibleName = $this->userService->getUserNameById($lead['responsible_user_id']);
        $message = sprintf(
            "Сделка \"%s\" создана %s. Ответственный - %s",
            $lead['name'],
            $createdAtFormat,
            $responsibleName
        );

        $this->noteService->addCommonNote('leads', $lead['id'], $message);
        $this->dataService->createDataInDB('leads', $lead['id'], $lead);

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    public function leadUpdate(Request $request)
    {
        $lead = $_POST['leads']['update'][0];
        $updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $updatedAtFormat = $updatedAt->setTimestamp($lead['updated_at'])->format('Y-m-d H:i:s');
        $oldLead = $this->dataService->getDataFromDB('leads', $lead['id']);
        $newValues = $this->dataService->getDataDifference($lead, $oldLead);
        if (!empty($newValues)) {
            array_walk($newValues, function (&$value, $key) {
                $value = "{$key}: {$value}";
            });

            $message = sprintf(
                "Сделка \"%s\" изменена %s. Новые значения: %s",
                $lead['name'],
                $updatedAtFormat,
                implode(', ', $newValues)
            );

            $this->noteService->addCommonNote('leads', $lead['id'], $message);
        }

        $this->dataService->updateDataInDB('leads', $lead['id'], $lead);

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    public function contactCreate(Request $request)
    {
        $contact = $_POST['contacts']['add'][0];
        $createdAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $createdAtFormat = $createdAt->setTimestamp($contact['created_at'])->format('Y-m-d H:i:s');
        $responsibleName = $this->userService->getUserNameById($contact['responsible_user_id']);
        $message = sprintf(
            "Контакт \"%s\" создан %s. Ответственный - %s",
            $contact['name'],
            $createdAtFormat,
            $responsibleName
        );

        $this->noteService->addCommonNote('contacts', $contact['id'], $message);
        $this->dataService->createDataInDB('contacts', $contact['id'],$contact);

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    public function contactUpdate(Request $request)
    {
        $contact = $_POST['contacts']['update'][0];
        $updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $updatedAtFormat = $updatedAt->setTimestamp($contact['updated_at'])->format('Y-m-d H:i:s');
        $oldContact = $this->dataService->getDataFromDB('contacts', $contact['id']);
        $newValues = $this->dataService->getDataDifference($contact, $oldContact);

        if (!empty($newValues)) {
            array_walk($newValues, function (&$value, $key) {
                $value = "{$key}: {$value}";
            });
            $message = sprintf(
                "Контакт \"%s\" изменен %s. Новые значения: %s",
                $contact['name'],
                $updatedAtFormat,
                implode(', ', $newValues)
            );

            $this->noteService->addCommonNote('contacts', $contact['id'], $message);
        }

        $this->dataService->updateDataInDB('contacts', $contact['id'], $contact);

        return new JsonResponse('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
