<?php

namespace Bumblebee\Compilation;

interface Expression
{

    /**
     * @return string
     */
    public function generate();

    /**
     * @return int
     */
    public function evaluationComplexity();

}
