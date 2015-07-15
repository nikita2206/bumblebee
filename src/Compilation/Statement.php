<?php

namespace Bumblebee\Compilation;


interface Statement
{

    /**
     * Returns PHP code for the statement
     *
     * @return string
     */
    public function generate();

}
