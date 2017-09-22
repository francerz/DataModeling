<?php
namespace Francerz\PhpModel;

class Multidimensional
{
    private $data;
    private $dims;

    public function __construct($data, $dimensions = array())
    {
        $this->data = $data;
        $this->dims = array();
        foreach ($dimensions as $dim) {
            $this->dims[$dim] = array(
                'rows' => ArrayHelper::valuesIndexer(
                    $data instanceof Collection ?
                        $data->getColumnValues($dim) :
                        ArrayHelper::getColumn($data, $dim)
                )
            );
        }
    }

    public function getDimensionValues($dimension, $filter = null)
    {
        $values = array_keys($this->dims[$dimension]['rows']);
        if (!empty($filter)) {
            $indexes = $this->filterIndexesByCoordinates($filter);
            $matches = array();
            foreach ($values as $k => $v) {
                if (count(array_intersect($this->dims[$dimension]['rows'][$v], $indexes)) > 0) {
                    $matches[] = $v;
                }
            }
            return $matches;
        }
        return $values;
    }

    public function getCell($dValue, $coords, callable $callback, $column = null)
    {
        if (empty($coords)) {
            if (is_string($callback) && strcasecmp($callback,'count') === 0) {
                return count($this->data);
            }
            $rows = $this->data;
        } else {
            $indexes = $this->filterIndexesByCoordinates($coords);
            if (count($indexes) === 0) return $dValue;
            if (is_string($callback) && strcasecmp($callback,'count') === 0) {
                return count($indexes);
            }
            $rows = ArrayHelper::filterKeys($this->data, $indexes);
        }

        if (is_null($column)) {
            return $callback($rows);
        } elseif ($rows instanceof CollectionInterface) {
            return $callback($rows->getColumnValues($column));
        } else {
            return $callback(ArrayHelper::getColumn($rows, $column));
        }

        return $dValue;
    }

    private function filterIndexesByCoordinates($coordinates)
    {
        $indexes = array();
        $isFirst = true;
        foreach ($coordinates as $ck => $cv)
        {
            if ($isFirst) {
                $indexes = $this->concatPropValues($this->dims[$ck]['rows'], $cv);
                $isFirst = false;
            } else {
                $indexes = array_intersect(
                    $indexes,
                    $this->concatPropValues($this->dims[$ck]['rows'],$cv)
                );
            }

            if (count($indexes) === 0) {
                break;
            }
        }
        return $indexes;
    }

    private function concatPropValues($object, $props)
    {
        if (is_array($props)) {
            $v = array();
            foreach($props as $prop) {
                $v = array_merge($v, $this->concatPropValues($object, $prop));
            }
            return $v;
        } else if (is_array($object) && isset($object[$props]) && is_array($object[$props])) {
            return $object[$props];
        }
        return array();
    }
}