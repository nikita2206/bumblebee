<?php

namespace Bumblebee\Compilation;


class IfStmt implements Statement
{

    /**
     * @var Expression
     */
    protected $condition;

    /**
     * @var Statement[]
     */
    protected $success;

    /**
     * @var Statement[]
     */
    protected $failure;

    public function __construct(Expression $condition, array $success, array $failure = [])
    {
        $this->condition = $condition;
        $this->success = $success;
        $this->failure = $failure;
    }

    public function generate()
    {
        $code = "if (" . $this->condition->generate() . ") {\n";

        foreach ($this->success as $stmt) {
            $code .= $stmt->generate() . "\n";
        }

        if ($this->failure) {
            $code .= "} else {\n";
            foreach ($this->failure as $stmt) {
                $code .= $stmt->generate() . "\n";
            }
        }

        return $code . "}\n";
    }

}
