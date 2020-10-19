<?php
declare(strict_types=1);

namespace MicroOdm\Mapper;


use JsonMapper;

/**
 * using jsonMapper as ODM hydrator.
 * Class MapperFactory
 * @package MicroOdm\Mapper
 */
class MapperFactory
{
    /**
     * @var JsonMapper
     */
    protected JsonMapper $mapper;

    private static MapperFactory $factory;

    private function __construct()
    {
        $this->mapper = new JsonMapper();
        $this->mapper->bStrictNullTypes = false;
    }

    /**
     * @return JsonMapper
     */
    static function getMapper(){
        if (self::$factory == null){
            self::$factory = new MapperFactory();
        }

        return self::$factory->mapper;
    }

}