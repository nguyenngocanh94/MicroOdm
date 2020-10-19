<?php
declare(strict_types=1);

namespace MicroOdm\Repository;

use ArrayObject;
use Exception;
use JsonMapper;
use MicroOdm\Annotations\AnnotationReader;
use MicroOdm\Entities\BaseEntity;
use MicroOdm\Exceptions\CanNotDeleteDocException;
use MicroOdm\Exceptions\CanNotInsertDocException;
use MicroOdm\Exceptions\CanNotParseToObjectException;
use MicroOdm\Exceptions\CanNotUpdateDocException;
use MicroOdm\Filter\Query;
use MicroOdm\Aggregate\AggregateQuery;
use MicroOdm\Mapper\MapperFactory;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;


abstract class BaseEntityRepository implements IRepository
{
    protected string $_entityTable;

    protected Database $_database;

    protected Collection $_collection;

    protected string $_class;

    protected JsonMapper $mapper;


    function findOne(string $identity)
    {
        try {
            $value = $this->_collection->findOne(['_id'=>new ObjectId($identity)]);
            $class = $this->_class;
            if ($value instanceof BaseEntity){
                $value->__original = clone $value;
                return $value;
            }
            if ($value==null){
                return null;
            }
            $entity = $this->mapper->map($value, new $class());
            $entity->__original = clone $entity;
            return $entity;
        }catch (Exception $exception){
            throw new CanNotParseToObjectException($exception->getMessage());
        }
    }

    function findBy(Query $query): array
    {
        try {
            list($condition, $option) = $query->toMongoQuery();
            $values = $this->_collection->find($condition, $option);
            $class = $this->_class;
            return $this->mapper->mapArray($values->toArray(), [], new $class());
        }catch (Exception $exception){
            throw new CanNotParseToObjectException($exception->getMessage());
        }
    }

    function count(Query $query): int
    {
        $condition = $query->getCondition();
        return $this->_collection->countDocuments($condition);
    }

    function update(Query $query, array $updated): int
    {
        $result = $this->_collection->updateMany($query->getCondition(), ['$set'=>$updated]);
        return $result->getModifiedCount();
    }

    public function delete(Query $query, array $options): int
    {
        return $this->_collection->deleteMany($query->getCondition(), $options)->getDeletedCount();

    }

    public function execute(AggregateQuery $query): ArrayObject
    {
        $result = $this->_collection->aggregate($query->getPipeline(), $query->getOptions());
        $returnArray = new ArrayObject();
        foreach ($result as $item){
            $returnArray->append($item);
        }

        return $returnArray;
    }


    public function __construct(string $entityClass, Database $database)
    {
        $this->_database =$database;
        $this->mapper = MapperFactory::getMapper();
        $this->_class = $entityClass;
        $this->_entityTable = AnnotationReader::getTableName($this->_class);
        $this->_collection = $this->_database->{$this->_entityTable};
    }

    /**
     * @param BaseEntity $entity
     * @return object
     * @throws CanNotUpdateDocException
     */
    public function updateOne(BaseEntity $entity)
    {
        try {
            $updated = $entity->getUpdateField();
            $result = $this->_collection->updateOne(['_id'=>new ObjectId($entity->getId()->__toString())], $updated);
            if ($result->isAcknowledged()){
                return (object)$entity;
            }

            throw new CanNotUpdateDocException($entity->getId().' can not be updated');
        }catch (Exception $exception){
            throw $exception;
        }
    }

    /**
     * @param BaseEntity $entity
     * @return bool
     * @throws CanNotDeleteDocException|Exception
     */
    public function deleteOne(BaseEntity $entity): bool
    {
        try {
            $result = $this->_collection->deleteOne(['id'=>$entity->getId()]);
            if ($result->isAcknowledged()){
                return true;
            }

            throw new CanNotDeleteDocException($entity->getId().' can not be deleted');
        }catch (Exception $exception){
            throw $exception;
        }
    }

    /**
     * @param BaseEntity $entity
     * @return BaseEntity
     * @throws CanNotInsertDocException|Exception
     */
    public function save(BaseEntity $entity)
    {
        try {
            if (null != $entity->getId()){
                $entity->setId(null);
            }
            $result = $this->_collection->insertOne($entity);
            if ($result->isAcknowledged()){
                $entity->setId($result->getInsertedId());
                return $entity;
            }

            throw new CanNotInsertDocException(get_class($entity). ' can not be inserted');
        }catch (Exception $exception){
            throw $exception;
        }
    }
}