<?php

namespace Bumblebee\Compilation;


class ArrayConstructor implements Expression
{

    /**
     * @var Expression[]
     */
    protected $map;

    public function __construct()
    {
        $this->map = [];
    }

    public function add($key, Expression $value)
    {
        $this->map[$key] = $value;
    }

    public function generate()
    {
        $code = "[";
        foreach ($this->map as $key => $val) {
            $code .= var_export($key, true) . " => " . $val->generate() . ",\n";
        }
        return $code . "]";
    }

}
