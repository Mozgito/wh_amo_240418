<?php

namespace App\Service;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Exceptions\AmoCRMApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContactService
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
     * @param int $contactId
     * @return array|null
     */
    public function getContactById(int $contactId)
    {
        $contact = null;

        try {
            $contact = $this->apiClient->contacts()->getOne($contactId);
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

        return $contact->toArray();
    }
}
