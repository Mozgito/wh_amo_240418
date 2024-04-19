<?php

namespace App\Service;

use App\Document\DBData;

class DataService
{
    private $dm;

    public function __construct(
        \Doctrine\ODM\MongoDB\DocumentManager $dm
    ) {
        $this->dm = $dm;
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param array<mixed, mixed> $data
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function createDataInDB(string $entityType, int $entityId, array $data)
    {
        $record = new DBData();
        $record->setEntityId($entityId);
        $record->setEntityType($entityType);
        $record->setData(serialize($data));
        $this->dm->persist($record);
        $this->dm->flush();
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @return array<mixed, mixed>|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getDataFromDB(string $entityType, int $entityId)
    {
        $record = $this->dm->getRepository(DBData::class)
            ->findBy(array('entityId' => $entityId, 'entityType' => $entityType));

        return empty($record) ? null : unserialize($record[0]->getData());
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param array<mixed, mixed> $data
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function updateDataInDB(string $entityType, int $entityId, array $data)
    {
        $record = $this->dm->getRepository(DBData::class)
            ->findBy(array('entityId' => $entityId, 'entityType' => $entityType));
        $record[0]->setData(serialize($data));
        $this->dm->persist($record[0]);
        $this->dm->flush();
    }
}
