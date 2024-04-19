<?php

namespace App\Service;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\NoteType\CommonNote;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NoteService
{
    private $apiClient;
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->apiClient = new AmoCRMApiClient(
            $params->get('app.clientid'),
            $params->get('app.secret')
        );
        $this->apiClient->setAccessToken(new LongLivedAccessToken($params->get('app.token')))
            ->setAccountBaseDomain($params->get('app.url'));
        $this->logger = $logger;
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param string $noteText
     */
    public function addCommonNote(string $entityType, int $entityId, string $noteText)
    {
        $notesCollection = new NotesCollection();
        $commonNote = new CommonNote();
        $commonNote->setEntityId($entityId)->setText($noteText);
        $notesCollection->add($commonNote);

        try {
            $this->apiClient->notes($entityType)->add($notesCollection);
        } catch (AmoCRMApiException $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'code' => $e->getCode(),
                    'description' => $e->getDescription(),
                    'requestInfo' => $e->getLastRequestInfo(),
                ]
            );
        }
    }
}
