<?php


namespace MicroOdm\Transistor;

use Exception;
use InvalidArgumentException;
use ReflectionException;
use ReflectionObject;

class Comparator
{

    /**
     * Compare 2 objects
     *
     * @param $o1
     * @param $o2
     * @param $strict bool in simple (==) or in strict way (===)
     * @return true objects are equals, false objects are differents
     */
    public static function equal($o1, $o2, $strict = false)
    {
        return $strict ? $o1 === $o2 : $o1 == $o2;
    }

    /**
     * Find the differences between 2 objects using Reflection.
     *
     * @param $o1
     * @param $o2
     * @return array Properties that have changed
     * @throws InvalidArgumentException|ReflectionException
     */
    public static function diff($o1, $o2)
    {
        if (!is_object($o1) || !is_object($o2)) {
            throw new InvalidArgumentException("Parameters should be of object type!");
        }

        $diff = [];
        if (get_class($o1) == get_class($o2)) {
            $o1Properties = (new ReflectionObject($o1))->getProperties();
            $o2Reflected = new ReflectionObject($o2);

            foreach ($o1Properties as $o1Property) {
                if ($o1Property->getName()!='__original'){
                    $o2Property = $o2Reflected->getProperty($o1Property->getName());
                    // Mark private properties as accessible only for reflected class
                    try {
                        $o1Property->setAccessible(true);
                        $o2Property->setAccessible(true);
                        if ($o1Property->isInitialized($o1) && $o2Property->isInitialized($o2)){
                            if (($value = $o1Property->getValue($o1)) != ($oldValue = $o2Property->getValue($o2))) {
                                $diff[$o1Property->getName()] = [
                                    'value' => $value,
                                    'old_value' => $oldValue
                                ];
                            }
                        }
                    }catch (Exception $e){
                        continue;
                    }
                }

            }
        }

        return $diff;
    }
}