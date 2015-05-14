<?php

namespace Bumblebee\Metadata;

class NumberFormatMetadata extends TypeMetadata
{

    protected $decimals;

    protected $decPoint;

    protected $thousandsSep;

    /**
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     */
    public function __construct($decimals = 0, $decPoint = ".", $thousandsSep = ",")
    {
        parent::__construct("number_format");

        $this->decimals = $decimals;
        $this->decPoint = $decPoint;
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * @return string
     */
    public function getDecPoint()
    {
        return $this->decPoint;
    }

    /**
     * @return string
     */
    public function getThousandsSep()
    {
        return $this->thousandsSep;
    }

}
