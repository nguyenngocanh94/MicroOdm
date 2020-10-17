<?php


namespace MicroOdm\Repository;


use ArrayObject;
use MicroOdm\Aggregate\AggregateQuery;
use MicroOdm\Entities\BaseEntity;
use MicroOdm\Filter\Query;

interface IRepository
{
    function findOne(string $identity);

    function findBy(Query $query): array;

    function count(Query $query): int;

    function save(BaseEntity $entity);

    function update(Query $query, array $updated): int;

    function updateOne(BaseEntity $entity);

    function delete(Query $query, array $options): int;

    function deleteOne(BaseEntity $entity): bool;

    function execute(AggregateQuery $query) : ArrayObject;
}