<?php

class SorryServiceNotAvailable extends Exception
{
    public static function becauseApiLimitReached($apiKey)
    {
        return new self("Api limit reached for api key $apiKey, try again after 1 minute.");
    }
}
