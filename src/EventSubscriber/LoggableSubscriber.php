<?php
namespace App\EventSubscriber;

use App\Helper\LoggableHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class LoggableSubscriber implements EventSubscriber
{
    private $loggableHelper;

    public function __construct(LoggableHelper $loggableHelper)
    {
        $this->loggableHelper = $loggableHelper;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs  $args)
    {
        $entityManager = $args->getEntityManager();
        $unit = $entityManager->getUnitOfWork();
        $this->loggableHelper->setEntityManager($entityManager);

        foreach ($unit->getScheduledEntityInsertions() as $entity) {
            $this->loggableHelper->processInsertion($entity);
        }

        foreach ($unit->getScheduledEntityUpdates() as $entity) {
            $this->loggableHelper->processUpdate($entity);
        }

        foreach ($unit->getScheduledEntityDeletions() as $entity) {
            $this->loggableHelper->processDeletion($entity);
        }

        foreach ($unit->getScheduledCollectionDeletions() as $collection) {
            $this->loggableHelper->processCollectionDeletion($collection);
        }

        foreach ($unit->getScheduledCollectionUpdates() as $collection) {
            $this->loggableHelper->processCollectionUpdate($collection);
        }
    }
}