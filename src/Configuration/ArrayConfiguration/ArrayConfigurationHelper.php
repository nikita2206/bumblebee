<?php

namespace Bumblebee\Configuration\ArrayConfiguration;

trait ArrayConfigurationHelper
{

    /**
     * @param string $value
     * @return array(null|string $type, string $remainingKey)
     */
    protected function extractType($value)
    {
        if (preg_match('!^(\w+?)\\((.+)\\)$!', trim($value), $match)) {
            return [$match[1], $match[2]];
        }

        return [null, $value];
    }

}
