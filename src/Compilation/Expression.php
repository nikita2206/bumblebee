<?php

namespace Bumblebee\Compilation;

interface Expression
{

    /**
     * Returns PHP code for the expression
     *
     * @return string
     */
    public function generate();

    /**
     * A complexity of evaluating the expression (calculated recursively with all the nested expressions added).
     * Can be used for making decisions in compilers if expression should be assigned to a variable or not
     * when expression can be used in more than one places. e.g. it's better to assign a method-call to
     * a variable and use it instead of calling a method twice.
     *
     * @return int
     */
    public function evaluationComplexity();

}
