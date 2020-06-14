<?php

namespace Francerz\PhpModel;

class Collection extends Item implements CollectionInterface
{
    private $items;

    /**
     *
     * @param $array $content
     */
    public function __construct($content = null)
    {
        if (is_null($content)) {
            parent::__construct();
            $this->items = array();
        } else {
            $data = ArrayHelper::getAssociative($content);
            $items = ArrayHelper::getIndexed($content);
            parent::__construct($data);
            $this->items = $items;
        }
    }

    /**
     * @param int|string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!is_int($offset)) {
            parent::offsetSet($offset, $value);
        }
        $this->items[$offset] = $value;
    }

    /**
     * @param int|string $offset
     * @return void
     */
    public function offsetExists($offset)
    {
        if (!is_int($offset)) {
            return parent::offsetExists($offset);
        }
        return isset($this->items[$offset]);
    }

    /**
     * @param int|string $offset
     * @return void
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset)) {
            return parent::offsetGet($offset);
        }
        if (!$this->offsetExists($offset)) {
            \trigger_error("Undefined offset $offset in collection.", E_USER_NOTICE);
            return null;
        }
        $this->coerceType($offset);
        return $this->items[$offset];
    }

    /**
     * @param int|string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!is_int($offset)) {
            parent::offsetUnset($offset);
        }
        if ($this->offsetExists($offset)) {
            unset($this->items[$offset]);
        }
    }

    /**
     * @return void
     */
    public function serialize()
    {
        return array(
            'items' => $this->itemsToArray(),
            '@parent' => parent::serialize()
        );
    }

    /**
     * @param mixed $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->addItems($data['items']);
        parent::unserialize($data['@parent']);
    }

    /**
     * Add the given items at the end of the list.
     *
     * @param array $newItems
     */
    public function addItems($newItems)
    {
        $this->items = array_merge(
            $this->items,
            $newItems
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            ['items'=>$this->items]
        );
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        return reset($this->items);
    }

    /**
     * @return Item
     */
    public function current()
    {
        $this->coerceType($this->key());
        return current($this->items);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * @return void
     */
    public function next()
    {
        return next($this->items);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return ($this->key() !== null);
    }

    /**
     * @param int $offset
     * @return void
     */
    protected function coerceType($offset)
    {
        $content = $this->items[$offset];
        if (is_array($content)) {
            if (ArrayHelper::isAssociative($content)) {
                $this->items[$offset] = new Item($content);
            } else {
                $this->items[$offset] = new Collection($content);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $item
     * @return void
     */
    public function push(ItemInterface $item)
    {
        $this->items[] = $item;
    }
    
    /**
     * @return void
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * @param [type] $column
     * @return void
     */
    public function getColumnValues($column)
    {
        return ArrayHelper::getColumn($this->items, $column);
    }

    /**
     * @param string[] $properties
     * @return void
     */
    public function itemsToArray($properties = null)
    {
        $items = array();
        foreach ($this->items as $key => $value) {
            if ($value instanceof ItemInterface) {
                $items[$key] = $value->toArray($properties);
            } else {
                $items[$key] = $value;
            }
        }
        return $items;
    }
    
    public function getMultimapWith($column_key)
    {
        $rootIndex = ArrayHelper::valuesIndexer(
            $this->getColumnValues($column_key)
        );

        $multimap = array();
        foreach($rootIndex as $value => $keys) {
            $multimap[$value] = ArrayHelper::filterKeys($this->items, $keys);
        }

        return new Multimap($multimap);
    }
}