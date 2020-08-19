<?php
namespace Francerz\PhpModel;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use JsonSerializable;

class Multidimensional implements ArrayAccess, JsonSerializable
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

    public function getCell($dValue, $coords, callable $callback, $column = null, $args = array())
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
            return call_user_func_array(
                $callback,
                array_merge(
                    [$rows],
                    $args
                ));
        } elseif ($rows instanceof CollectionInterface) {
            return call_user_func_array(
                $callback,
                array_merge(
                    [$rows->getColumnValues($column)],
                    $args
                ));
        } else {
            return call_user_func_array(
                $callback,
                array_merge(
                    [ArrayHelper::getColumn($rows, $column)],
                    $args
                ));
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

    public function offsetExists($offset)
    {

    }
    public function offsetGet($offset)
    {
        if (is_array($offset)) {
            if (count($offset) == 0) {
                return $this->data;
            }
            $indexes = $this->filterIndexesByCoordinates($offset);
            $data = ArrayHelper::filterKeys($this->data, $indexes);
            return new DataGroup($data);
        } else {
            throw new InvalidArgumentException("ERRORSISSIMO 2gUqk3983tjh");
        }
    }
    public function offsetSet($offset, $value)
    {

    }
    public function offsetUnSet($offset)
    {

    }
    public function jsonSerialize()
    {
        return $this->data;
    }
}

class DataGroup implements Countable
{
    private $data;
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    private static function filter(array $data, callable $filter = null)
    {
        if (is_callable($filter)) {
            return array_filter($data, $filter, 0);
        } else {
            return $data;
        }
    }
    public function sum(string $field, callable $filter = null)
    {
        $res = 0;
        $data = static::filter($this->data, $filter);
        foreach ($data as $d) {
            $res += $d->{$field};
        }
        return $res;
    }
    public function first(string $field, callable $filter = null)
    {
        $data = static::filter($this->data, $filter);
        if (count($data) > 0) {
            reset($data);
            $f = current($data);
            if (is_object($f)) {
                return $f->{$field};
            }
        }
        return;
    }
    public function reduce($initial, callable $function) {
        foreach ($this->data as $d) {
            $initial = $function($initial, $d);
        }
        return $initial;
    }
    public function mean(string $field, callable $filter = null)
    {
        $sum = $this->sum($field, $filter);
        $data = static::filter($this->data, $filter);
        return $sum / count($data);
    }
    public function unique(string $field, callable $filter = null)
    {
        $filter = static::filter($this->data, $filter);
        $data = array_unique(array_column($filter, $field));
        return $data;
    }
    public function count() {
        return count($this->data);
    }
    public function __get($name) {
        return $this->first($name);
    }
}
