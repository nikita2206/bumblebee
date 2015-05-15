<?php

namespace Bumblebee\Compilation;


abstract class Statement
{

    /**
     * @return string
     */
    public abstract function generate();

}
