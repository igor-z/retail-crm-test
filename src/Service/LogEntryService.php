<?php
namespace App\Service;

use App\Entity\LogEntry;
use App\Helper\EntityHelper;
use App\Repository\LogEntryRepository;
use Doctrine\ORM\EntityManager;

class LogEntryService
{
    private $logEntryRepository;
    private $entityHelper;

    public function __construct(LogEntryRepository $logEntryRepository, EntityHelper $entityHelper)
    {
        $this->logEntryRepository = $logEntryRepository;
        $this->entityHelper = $entityHelper;
    }

    public function getEntityEntries(EntityManager $entityManager, $entity)
    {
        $logEntries = $this->logEntryRepository->findBy([
            'entity_class' => get_class($entity),
            'entity_id' => $this->entityHelper->getEntityId($entityManager, $entity),
        ]);

        $assocEntities = [];

        foreach ($logEntries as $index => $logEntry) {
            if (in_array($logEntry->getType(), ['association', 'disassociation'])) {
                $changes = $logEntry->getChanges();

                $assocEntities[$changes['class']][] = $changes['id'];
                unset($logEntries[$index]);
            }
        }


        foreach ($assocEntities as $assocClass => $assocEntityIds) {
            $logEntries = array_merge($logEntries, $this->logEntryRepository->findBy([
                'entity_class' => $assocClass,
                'entity_id' => $assocEntityIds,
            ]));
        }

        usort($logEntries, function($entry1, $entry2) {
            /**
             * @var LogEntry $entry1
             * @var LogEntry $entry2
             */

            if ($entry1->getId() < $entry2->getId())
                return 1;
            elseif ($entry1->getId() > $entry2->getId())
                return -1;
            else
                return 0;
        });

        return $logEntries;
    }
}