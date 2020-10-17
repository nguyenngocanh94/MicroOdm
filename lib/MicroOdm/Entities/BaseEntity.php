<?php
declare(strict_types=1);

namespace MicroOdm\Entities;


use MongoDB\BSON\ObjectId;

abstract class BaseEntity
{
    // for identity
    protected ?ObjectId $id;
    // time
    protected ?int $ts;
    protected ?int $updateTs;
    /**
     * @return int
     */
    public function getUpdateTs(): int
    {
        return $this->updateTs;
    }

    /**
     * @param ?ObjectId $id
     */
    public function setId(?ObjectId $id): void
    {
        $this->id = $id;
    }

    /**
     * @return ObjectId
     */
    public function getId(): ObjectId
    {
        return $this->id;
    }

    /**
     * @param int $ts
     */
    public function setTs(int $ts): void
    {
        $this->ts = $ts;
    }

    /**
     * @return int
     */
    public function getTs(): int
    {
        return $this->ts;
    }

    /**
     * @param int $updateTs
     */
    public function setUpdateTs(int $updateTs): void
    {
        $this->updateTs = $updateTs;
    }

    public abstract function getUpdateField() : array;
}