<?php
declare(strict_types=1);

namespace MicroOdm\Aggregate;

class AggregateQuery
{
    private array $pipeline;
    private array $options;

    public function __construct(array $pipeline, array $options){
        $this->options = $options;
        $this->pipeline = $pipeline;
    }

    public function setOption(array $options) : self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getPipeline(): array
    {
        return $this->pipeline;
    }
}