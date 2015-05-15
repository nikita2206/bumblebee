<?php

namespace Bumblebee\Compilation;

interface Expression
{

    /**
     * @return string
     */
    public function generate();

}
