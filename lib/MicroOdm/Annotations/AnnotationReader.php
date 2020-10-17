<?php
declare(strict_types=1);

namespace MicroOdm\Annotations;

use Doctrine\Common\Annotations\AnnotationReader as DocReader;
use MicroOdm\NamingConvert\NamingStandardConverter;


class AnnotationReader
{
    static function getTableName(string $class) : string{
        $reader = new DocReader;
        $reflector = new \ReflectionClass($class);
        $annotations = $reader->getClassAnnotations($reflector);
        foreach ($annotations as $annotation){
            if ($annotation instanceof Table){
                return $annotation->name;
            }
        }
        $shortName = $reflector->getShortName();
        return NamingStandardConverter::CamelToSnake($shortName);
    }


    static function getUnPersistField(object $instance) : array{
        $array = [];
        $reader = new DocReader;
        $reflector = new \ReflectionClass($instance);
        $reflectionProperties = $reflector->getProperties();
        foreach ($reflectionProperties as $reflectionProperty){
            $annotation = $reader->getPropertyAnnotation($reflectionProperty, UnPersist::class);
            if ($annotation!=null){
                $array[] = $reflectionProperty->getName();
            }
        }

        return $array;
    }
}