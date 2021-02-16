<?php


namespace XmlMapper\Elements;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use XmlMapper\Exceptions\XmlMapperException;

abstract class NodeArray implements IteratorAggregate, Countable
{
	/**
	 * @var string - type of items in array
	 */
    protected string $className;

    /**
	 * @var array items in array
	 */
    protected array $items = [];

	/**
	 * Add item into Node Array
	 * @param $item
	 * @throws XmlMapperException
	 */
    public function addItem($item) {
        if ($item instanceof $this->className) {
            $this->items[] = $item;
        } else {
            throw new XmlMapperException('This item not allowed here');
        }
    }

    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className) {
        $this->className = $className;
    }

	/**
	 * @param $index
	 * @return mixed|null
	 */
    public function getItem($index) {
    	return $this->items[$index] ?? null;
	}

    /**
     * @return array|Traversable
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @param array|Traversable $items
     */
    public function setItems($items) {
        $this->items = $items;
    }

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator($this->items);
	}

	/**
	 * @return int
	 */
	public function count(): int {
		return count($this->items);
	}
}
