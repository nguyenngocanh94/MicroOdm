<?php
declare(strict_types=1);

namespace MicroOdm\Aggregate;

class AggregateQueryBuilder
{
    private array $pipeline = [];

    public static function create(){
        return new self();
    }

    function getPipeline() : array
    {
        return $this->pipeline;
    }

    private function pushStage(string $stageName,array $param) : AggregateQueryBuilder
    {
        $this->pipeline[] = [
            $stageName => $param
        ];
        return $this;
    }

    public function match(array $param): AggregateQueryBuilder
    {
        return $this->pushStage('$match', $param);
    }

    public function group(array $param): AggregateQueryBuilder
    {
        return $this->pushStage('$group', $param);
    }

    public function addFields(array $param): AggregateQueryBuilder
    {
        return $this->pushStage('$addFields', $param);
    }

    public function unwind(array $param): AggregateQueryBuilder
    {
        return $this->pushStage('$unwind', $param);
    }



    public function lookup(array $param)
    {
        return $this->pushStage('$lookup', $param);
    }

    public function project(... $fieldNames): AggregateQueryBuilder
    {
        $acc = [];
        foreach ($fieldNames as $fieldName) {
            $acc = array_merge($acc, [$fieldName=>1]);
        }
        return $this->pushStage('$project', $acc);
    }

    public function sample(int $size): AggregateQueryBuilder
    {
        return $this->pushStage('$sample', ['size'=>$size]);
    }

    public function sort(array $param): AggregateQueryBuilder
    {
        return $this->pushStage('$sort', $param);
    }

    public function skip(int $step): AggregateQueryBuilder
    {
        $this->pipeline[] = [
            '$skip' => $step
        ];
        return $this;
    }

    public function count(array $options) : AggregateQuery{
        $finalCountStage = [
            '$count' => 'count'
        ];
        $pipelineWithoutRedundantStages = $this->pipeline;

        /**
         * we can remove some stages if they are at the end of the pipeline
         */
        $stagesThatCanBeRemoveFromTheEndOfPipelineWithoutChangingTheTotal =
            ['$lookup', '$sort', '$addFields', '$project'];

        while (true) {
            $finalStage = $pipelineWithoutRedundantStages[count($pipelineWithoutRedundantStages) - 1];
            $finalStageKey = array_keys($finalStage)[0];
            if (in_array($finalStageKey, $stagesThatCanBeRemoveFromTheEndOfPipelineWithoutChangingTheTotal)) {
                array_pop($pipelineWithoutRedundantStages);
            } else {
                break;
            }
        }
        $countPipeline = array_merge(
            $pipelineWithoutRedundantStages,
            [$finalCountStage]
        );

        return new AggregateQuery($countPipeline, $options);
    }

    public function take(int $limit): self
    {
        $this->pipeline[] = [
            '$limit' => $limit
        ];
        return $this;
    }

    public final function toQuery(){
        return new AggregateQuery($this->pipeline, []);
    }
    /**
     * magic method, not suggest
     * @param $functionName
     * @param $params
     * @return AggregateQueryBuilder
     */
    function __call($functionName, $params): AggregateQueryBuilder
    {
        $param = count($params) === 1 ? $params[0] : null;
        return $this->pushStage('$' . $functionName, $param);
    }
}