<?php

namespace App;

class Location
{
    private $lat;
    private $long;
    private $alt;

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return mixed
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @return int
     */
    public function getAlt()
    {
        return $this->alt;
    }

    public function __construct($lat, $long, $alt = 0)
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->alt = $alt;
    }

    public function __toString()
    {
        return "lat = {$this->lat}, long = {$this->long}";
    }
}
