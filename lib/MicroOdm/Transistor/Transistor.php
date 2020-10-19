<?php

namespace MicroOdm\Transistor;


use MicroOdm\Annotations\AnnotationReader;
use MicroOdm\Common\Enum;
use MicroOdm\Mapper\MapperFactory;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Type;

trait Transistor {

    /**
     * Convert the `_created` UTCDateTime stamp to PHP native DateTime object
     */
    function getCreatedDateTime() {
        if (!$this->_created) {
            throw new \OutOfBoundsException("No creation time registered yet");
        }
        return $this->_created->toDateTime();
    }

    /**
     * Convert the `_lastModified` UTCDateTime stamp to PHP native DateTime object
     * Note: This does not get updated unless the object is loaded again
     */
    function getLastModifiedDateTime() {
        if (!$this->_lastModified) {
            throw new \OutOfBoundsException("No updates registered yet");
        }
        return $this->_lastModified->toDateTime();
    }

    /**
     * Returns an associative array of "properties", and their values, that should
     * be saved for this object.
     * This method can be overwritten in case you'd like to "hide" certain properties,
     * or otherwise mask them.
     */
    function __getObjectData() {
        $props = get_object_vars($this);

        /* These aren't inserted/updated as values, but magically treated by this trait
         * and/or set/updated by MongoDB */
        unset($props["__original"]);
        unset($props["_lastModified"]);
        unset($this->__original->_lastModified);
        $unPersists = AnnotationReader::getUnPersistField($this);
        foreach ($unPersists as $unPersist){
            unset($props[$unPersist]);
        }
        $props = self::toArray($props);

        return $props;
    }

    private function toArray(array $array) : array{
        foreach ($array as $key=>&$value){
            if (is_object($value)){
                if (in_array(Arrayable::class, class_uses($value))){
                    $value = $value->toArray();
                }elseif(!$value instanceof Type){
                    if ($value instanceof Enum){
                        $value = $value->value();
                    }else{
                        $arr = get_object_vars($value);
                        $value = self::toArray($arr);
                    }
                }
            }elseif(is_array($value)){
                $value = self::toArray($value);
            }
        }
        return $array;
    }

    /**
     * Implements the bsonUnserialize method from BSON\Persistable.
     * Will set all root keys from the document as top-level object properties.
     * Stores the original values in a magical `__original` property to be used later
     * for change tracking of this object.
     */
    function bsonUnserialize(array $array) {
        $object = MapperFactory::getMapper()->map((object)$array, $this);
        $entity = clone $object;
        $entity->__original = $object;
        return $entity;
    }

    /**
     * Here be dragons.
     * Attempts to diff the current state of the object to its original state,
     * and generate MongoDB update statement out of it.
     *
     * The check is recursive, so deeply nested arrays "should work" (tm).
     */
    function _bsonSerializeRecurs(&$updated, $newData, $oldData, $keyfix = "") {
        foreach($newData as $k => $v) {

            /* A new field -- likely a on-the-fly schema upgrade */
            if (!isset($oldData[$k])) {
                $updated['$set']["$keyfix$k"] = $v;
            }

            /* Changed value */
            elseif ($oldData[$k] != $v) {
                /* Not-empty arrays need to be recursively checked for changes */
                if (is_array($v) && $oldData[$k] && $v) {
                    $this->_bsonSerializeRecurs($updated, $v, $oldData[$k], "$keyfix$k.");
                } else {
                    /* Normal changes in keys can simply be overwritten.
                     * This applies to previously empty arrays/documents too */
                    $updated['$set']["$keyfix$k"] = $v;
                }
            }
        }

        /* Data that used to exist, but now doesn't, needs to be $unset */
        foreach($oldData as $k => $v) {
            if (!isset($newData[$k])) {
                /* Removed field -- likely a on-the-fly schema upgrade */
                $updated['$unset']["$keyfix$k"] = "";
                continue;
            }
        }
    }

    /**
     * Implements the bsonSerialize method from BSON\Persistable.
     *
     * Takes all object properties and stores them as propertyname=>propertyvalue
     * in a BSON Document.
     * Automatically detects if this is a fresh object, or is being updated.
     * Calculates differences for updates based on the `__original` property that was
     * set during the unserialization of this object.
     *
     * Automatically sets `_created` property on this object, and stores it in the
     * document.
     * Automatically updates the `_lastModified` property in the document.
     */
    function bsonSerialize() {
        /* temporary workaround for https://jira.mongodb.org/browse/PHPC-545 */
        $this->__pclass = new Binary(get_class($this), Binary::TYPE_USER_DEFINED);

        $serialized = $this->__getObjectData();
        return array_filter($serialized, function ($v,$k){
            return $v !== null;
        }, ARRAY_FILTER_USE_BOTH );
    }

    function getUpdateField() : array{
        $diffs = Comparator::diff($this, $this->__original);
        $updated = [];
        foreach ($diffs as $key=>$value){
            $updated = array_merge($updated, [$key => $value['value']]);
        }
        return ['$set'=>$updated];
    }
}