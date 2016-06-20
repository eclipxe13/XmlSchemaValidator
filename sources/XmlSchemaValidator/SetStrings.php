<?php

namespace XmlSchemaValidator;

/**
 * This is an utility class to keep a set of strings
 * Does not throw any error or warning
 * Convert objects to strings and any invalid argument is casted as an empty string
 * Does not allow to add an empty string
 *
 * @access private
 * @package XmlSchemaValidator
 */
class SetStrings implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected $members;

    public function __construct(array $members = [])
    {
        $this->clear();
        $this->addAll($members);
    }

    public function clear()
    {
        $this->members = [];
    }

    public function addAll(array $members)
    {
        foreach ($members as $member) {
            $this->add($member);
        }
    }

    public function all()
    {
        return array_keys($this->members);
    }

    public function add($member)
    {
        $member = $this->cast($member);
        if ('' === $member) {
            return false;
        }
        if ($this->contains($member)) {
            return false;
        }
        $this->members[$member] = null;
        return true;
    }

    public function cast($member)
    {
        if (is_object($member)) {
            $member = is_callable([$member, '__toString']) ? (string) $member : '';
        }
        return strval($member);
    }

    public function contains($member)
    {
        $member = $this->cast($member);
        return array_key_exists($member, $this->members);
    }

    public function remove($member)
    {
        $member = $this->cast($member);
        unset($this->members[$member]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    public function count()
    {
        return count($this->members);
    }
}
