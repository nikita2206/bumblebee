<?php

namespace Bumblebee\Compilation;

class UnsetVariable implements Statement
{

    /**
     * @var AssignableExpression[]
     */
    protected $vars;

    /**
     * @param AssignableExpression[] $vars
     */
    public function __construct($vars)
    {
        $this->vars = $vars;
    }

    public function generate()
    {
        $code = "unset(";
        $vars = [];
        foreach ($this->vars as $var) {
            $vars[] = $var->generate();
        }
        return $code . implode(",", $vars) . ");";
    }

}
