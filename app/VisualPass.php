<?php

namespace App;

use DateTime;
use DateTimeZone;
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

    public function getFormattedTimeTillTheNextSatellite()
    {

        $timezone = new DateTimeZone('UTC');
        $now      = new DateTime('now', $timezone);
        $then     = new DateTime("@{$this->start_utc}", $timezone);

        $interval = $now->diff($then);

        $days    = $interval->format('%a');
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%s');

        return implode(':', [
            $days < 10 ? '0' . $days : $days,
            $hours < 10 ? '0' . $hours : $hours,
            $minutes < 10 ? '0' . $minutes : $minutes,
            $seconds < 10 ? '0' . $seconds : $seconds,
        ]
        );
    }

    public function jsonSerialize()
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'start_utc' => $this->start_utc,
            'end_utc'   => $this->end_utc,
            'formatted_countdown' => $this->getFormattedTimeTillTheNextSatellite(),
            'duration'  => $this->duration,
            'magnitude' => $this->magnitude,
        ];
    }
}
