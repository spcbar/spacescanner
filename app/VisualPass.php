<?php

namespace App;

use JsonSerializable;

class VisualPass implements JsonSerializable
{
    public $id;

    public $name;

    public $start_utc;

    public $end_utc;

    public $duration;

    public $magnitude;

    public function __construct($id, $name, $start_utc, $end_utc, $duration, $magnitude)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->start_utc = $start_utc;
        $this->end_utc   = $end_utc;
        $this->duration  = $duration;
        $this->magnitude = $magnitude;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_utc' => $this->start_utc,
            'end_utc' => $this->end_utc,
            'duration' => $this->duration,
            'magnitude' => $this->magnitude,
        ]; // todo add extra info like date and period
    }
}
