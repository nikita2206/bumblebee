<?php

namespace Bumblebee\Compilation;


interface Statement
{

    /**
     * @return string
     */
    public function generate();

}
