<?php

namespace App;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;

class Repository
{
    /** @var EasyDB */
    private $db;

    public function __construct($host, $dbname, $login, $password)
    {

        $this->db = Factory::create(
            "mysql:host=$host;dbname=$dbname",
            $login,
            $password
        );
    }
}
