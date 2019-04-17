<?php
namespace App\Helper;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

class LoggableHelper
{
    /** @var EntityManager */
    private $entityManager;
    private $entityHelper;

    public function __construct(EntityHelper $entityHelper)
    {
        $this->entityHelper = $entityHelper;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function insertLogEntry($entity, string $type)
    {
        $logEntry = new LogEntry();
        $logEntry->setType($type);
        $logEntry->setChanges($this->getEntityChangeSet($entity));
        $logEntry->setEntityClass(get_class($entity));
        $logEntry->setEntityId($this->entityHelper->getEntityId($this->entityManager, $entity));

        $this->saveLogEntry($logEntry);
    }

    public function processUpdate($entity)
    {
        if (!$this->entityHelper->isLoggable($entity)) {
            return;
        }

        $this->insertLogEntry($entity, 'update');
    }

    public function processInsertion($entity)
    {
        if (!$this->entityHelper->isLoggable($entity)) {
            return;
        }

        $this->insertLogEntry($entity, 'insertion');
    }

    public function processDeletion($entity)
    {
        if (!$this->entityHelper->isLoggable($entity)) {
            return;
        }

        $logEntry = new LogEntry();
        $logEntry->setType('deletion');
        $logEntry->setChanges($this->getEntityOriginalData($entity));
        $logEntry->setEntityClass(get_class($entity));
        $logEntry->setEntityId($this->entityHelper->getEntityId($this->entityManager, $entity));

        $this->saveLogEntry($logEntry);
    }

    public function processCollectionDeletion(PersistentCollection $collection)
    {
        if (!$this->entityHelper->isLoggable($collection->getOwner())) {
            return;
        }

        $owner = $collection->getOwner();

        foreach ($collection as $entity) {
            $logEntry = new LogEntry();
            $logEntry->setType('disassociation');
            $logEntry->setChanges([
                'id' => $this->entityHelper->getEntityId($this->entityManager, $entity),
                'class' => get_class($entity),
            ]);
            $logEntry->setEntityClass(get_class($owner));
            $logEntry->setEntityId($this->entityHelper->getEntityId($this->entityManager, $owner));

            $this->saveLogEntry($logEntry);
        }
    }

    public function processCollectionUpdate(PersistentCollection $collection)
    {
        if (!$this->entityHelper->isLoggable($collection->getOwner())) {
            return;
        }

        $owner = $collection->getOwner();

        foreach ($collection->getInsertDiff() as $entity) {
            $logEntry = new LogEntry();
            $logEntry->setType('association');
            $logEntry->setChanges([
                'id' => $this->entityHelper->getEntityId($this->entityManager, $entity),
                'class' => get_class($entity),
            ]);
            $logEntry->setEntityClass(get_class($owner));
            $logEntry->setEntityId($this->entityHelper->getEntityId($this->entityManager, $owner));

            $this->saveLogEntry($logEntry);
        }

        foreach ($collection->getDeleteDiff() as $entity) {
            $logEntry = new LogEntry();
            $logEntry->setType('disassociation');
            $logEntry->setChanges([
                'id' => $this->entityHelper->getEntityId($this->entityManager, $entity),
                'class' => get_class($entity),
            ]);
            $logEntry->setEntityClass(get_class($owner));
            $logEntry->setEntityId($this->entityHelper->getEntityId($this->entityManager, $owner));

            $this->saveLogEntry($logEntry);
        }
    }

    private function getEntityChangeSet($entity)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);
        foreach ($changeSet as $property => $value) {
            if ($this->entityHelper->isIgnored($entity, $property) || $metadata->hasAssociation($property)) {
                unset($changeSet[$property]);
            }
        }

        return $changeSet;
    }

    private function getEntityOriginalData($entity)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity);
        foreach ($originalData as $property => $value) {
            if ($this->entityHelper->isIgnored($entity, $property) || $metadata->hasAssociation($property)) {
                unset($originalData[$property]);
            }
        }

        return $originalData;
    }

    private function saveLogEntry($logEntry)
    {
        $this->entityManager->persist($logEntry);
        $this->entityManager->getUnitOfWork()->computeChangeSet($this->entityManager->getClassMetadata(get_class($logEntry)), $logEntry);
    }
}