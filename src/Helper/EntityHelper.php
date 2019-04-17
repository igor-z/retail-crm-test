<?php
namespace App\Helper;

use App\Annotation\Log\Ignored;
use App\Annotation\Log\Loggable;
use App\Exception\EntityNotSupportedException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use ReflectionProperty;

class EntityHelper
{
    private $annotationsReader;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    public function getEntityId(EntityManager $entityManager, $entity)
    {
        $metadata = $entityManager->getClassMetadata(get_class($entity));
        $pk = $metadata->getSingleIdentifierFieldName();
        if (isset($metadata->fieldMappings[$pk])) {
            $type = Type::getType($metadata->fieldMappings[$pk]['type']);
            if (in_array($type->getName(), [Type::INTEGER, Type::SMALLINT])) {
                return $metadata->getReflectionProperty($pk)->getValue($entity);
            }
        } else {
            throw new EntityNotSupportedException("Entity primary key must be integer");
        }
    }

    public function isLoggable($entity)
    {
        return (bool) $this->annotationsReader->getClassAnnotation(new ReflectionClass(get_class($entity)), Loggable::class);
    }

    public function isIgnored($entity, string $property)
    {
        return (bool) $this->annotationsReader->getPropertyAnnotation(new ReflectionProperty(get_class($entity), $property), Ignored::class);
    }
}