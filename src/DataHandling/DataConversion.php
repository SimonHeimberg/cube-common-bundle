<?php

namespace CubeTools\CubeCommonBundle\DataHandling;

class DataConversion
{
    /**
     * Convert texts 'true', 'false', 'null' and numeric values to php data.
     *
     * @param any $value value to convert (it is converted itself)
     */
    public static function dataTextToData(&$value /*, $key*/)
    {
        switch ($value) {
            case 'true':
                $value = true;
                break;
            case 'false':
                $value = false;
                break;
            case 'null':
                $value = null;
                break;
            case is_numeric($value):
                $value = $value + 0; // converts to float or integer
        }
    }

    /**
     * Convert texts 'true', 'false', 'null' and numeric values to php data in the array elements (recursive).
     *
     * Helpful when data is sent by a ajax request.
     *
     * @param array $array Array (with arrays) of data to convert
     *
     * @return bool true if no failure occurred (see @array_walk_recursive)
     */
    public static function dataTextToDataInArray(array &$array)
    {
        return array_walk_recursive($array, array(static::class, 'dataTextToData'));
    }
}
