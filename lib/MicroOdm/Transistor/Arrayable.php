<?php


namespace MicroOdm\Transistor;


use MongoDB\BSON\Type;

trait Arrayable
{
    function toArray(){
        $kv = get_object_vars($this);
        foreach ($kv as $k=>&$v){
            if (is_object($v)){
                if (in_array( Arrayable::class, class_uses($v))){
                    $v = $v->toArray();
                }elseif(!$v instanceof Type){
                    $v = get_object_vars($v);
                }
            }elseif(is_array($v)){
                $v = $this->parseArray($v);
            }
        }

        return $kv;
    }

    function parseArray(array $arr){
        foreach ($arr as $k => $v){
            if (is_object($v)){
                if (in_array( Arrayable::class, class_uses($v))){
                    $v = $v->toArray();
                }elseif(!$v instanceof Type){
                    $v = get_object_vars($v);
                }
            }elseif(is_array($v)){
                $this->parseArray($v);
            }
        }

        return $arr;
    }
}