<?php
declare(strict_types=1);

namespace MicroOdm\Filter;


final class Query
{
    private array $filters;
    private array $orders;
    private int $offset;
    private int $limit;

    public static function create() : self{
        return new self();
    }

    public function __construct()
    {
        $this->filters = [];
        $this->orders   = [];
        $this->offset  = 0;
        $this->limit   = 10;
    }


    public function equal(string $field, $value) : Query{
        $this->filters = array_merge($this->filters, [$field=>$value]);
        return $this;
    }

    public function notEqual(string $field, $value): Query{
        $this->filters = array_merge($this->filters, [$field=>['$ne'=>$value]]);
        return $this;
    }

    public function greater(string $field, $value) : Query{
        $this->filters = array_merge($this->filters, [$field=>['$gt'=>$value]]);
        return $this;
    }

    public function less(string $field, $value): Query{
        $this->filters = array_merge($this->filters, [$field=>['$lt'=>$value]]);
        return $this;
    }

    public function orderBy(string $field, string $type = "ASC"): Query
    {
        if ($type=='ASC'){
            $this->orders = array_merge($this->orders, [$field=>1]);
        }else{
            $this->orders = array_merge($this->orders, [$field=>-1]);
        }

        return $this;
    }

    public function limit(int $limit) : Query{
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset) : Query{
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return array
     */
    public function getCondition(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    public function toMongoQuery() : array{
        $options = array_merge($this->orders, ['limit'=>$this->limit]);
        $options = array_merge($options, ['offset'=>$this->offset]);
        return array($this->filters, $options);
    }
}