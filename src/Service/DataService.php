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
        $record = $this->dm->getRepository(DBData::class)
            ->findBy(array('entityId' => $entityId, 'entityType' => $entityType));
        if (empty($record)) {
            $record = new DBData();
            $record->setEntityId($entityId);
            $record->setEntityType($entityType);
            $record->setData(serialize($data));
            $this->dm->persist($record);
            $this->dm->flush();
        }
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

    /**
     * @param array $newData
     * @param array $oldData
     * @return array
     */
    public function getDataDifference(array $newData, array $oldData)
    {
        $diff = [];
        $skipFields = ['old_status_id', 'last_modified', 'updated_at', 'old_responsible_user_id', 'linked_leads_id'];

        foreach ($newData as $newDataKey => $newDataValue) {
            if (in_array($newDataKey, $skipFields)) {
                continue;
            }

            if (isset($oldData[$newDataKey])) {
                if ('tags' === $newDataKey) {
                    $tags = array_udiff($newDataValue, $oldData['tags'], [$this, 'compareTags']);

                    foreach ($tags as $tagKey => $tag) {
                        $diff['tag' . $tagKey] = $tag['name'];
                    }
                } elseif ('custom_fields' === $newDataKey) {
                    $customFields = array_udiff($newDataValue, $oldData['custom_fields'], [$this, 'compareCustomFields']);

                    foreach ($customFields as $cf) {
                        if (is_array($cf['values'][0])) {
                            $diff[$cf['name']] = $cf['values'][0]['value'];
                        } else {
                            $diff[$cf['name']] = $cf['values'][0];
                        }
                    }
                } else {
                    if ($newDataValue !== $oldData[$newDataKey]) {
                        $diff[$newDataKey] = $newDataValue;
                    }
                }
            } else {
                $diff[$newDataKey] = $newDataValue;
            }

        }

        return $diff;
    }

    private function compareTags($a, $b)
    {
        return ($a['id'] - $b['id']);
    }

    private function compareCustomFields(array $a, array $b){
        if (
            $a['id'] === $b['id']
            && (
                (is_array($a['values'][0]) && $a['values'][0]['value'] === $b['values'][0]['value'])
                || (!is_array($a['values'][0]) && $a['values'][0] === $b['values'][0])
            )){
            return 0;
        } else {
            return -1;
        }
    }
}
