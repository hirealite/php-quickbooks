<?php

namespace PhpQuickbooks;

use stdClass;

class AttributeCollection
{
    /**
     * @var \stdClass
     */
    protected $attributes = null;

    /**
     * @var \stdClass
     */
    protected $original = null;

    /**
     * AttributeCollection constructor.
     *
     * @param \stdClass $attributes
     */
    public function __construct(stdClass $attributes = null)
    {
        $this->attributes = new stdClass();

        if (!$attributes) {
            $attributes = new stdClass();
        }

        $this->fill($attributes, true);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function camelCase(string $key): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    public function __get($key)
    {
        return $this->getAttribute($key) ?? $this->getAttribute($this->camelCase($key));
    }

    public function __set($key, $value)
    {
        if (property_exists($this->attributes, $key)) {
            $this->attributes->$key = $value;
        }
    }

    public function toArray()
    {
        $array = [];

        foreach ($this->attributes as $key => $value) {
            $array[$key] = ($value instanceof AttributeCollection) ? $value->toArray() : $value;
        }

        return $array;
    }

    protected function fill($data, $initial = false)
    {
        foreach ($data as $key => $value) {
            $this->attributes->$key = ($value instanceof stdClass) ? new AttributeCollection($value) : $value;
        }

        if ($initial) {
            $this->original = $this->cloneAttributes();
        }

        return $this;
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if ($value instanceof AttributeCollection) {
                $sub_dirty = $value->getDirty();

                if(count($sub_dirty)) {
                    $dirty[$key] = $sub_dirty;
                }
            } else {
                if (!property_exists($this->original, $key)) {
                    $dirty[$key] = $value;
                } elseif ($value != $this->original->$key) {
                    $dirty[$key] = $value;
                }
            }
        }

        return $dirty;
    }

    private function getAttribute($key)
    {
        if (property_exists($this->attributes, $key)) {
            return $this->attributes->$key;
        }

        return null;
    }

    protected function cloneAttributes()
    {
        $cloned = new stdClass;

        foreach($this->attributes as $key => $value) {
            $cloned->$key = ($value instanceof AttributeCollection) ? $value->cloneAttributes() : $value;
        }

        return $cloned;
    }
}