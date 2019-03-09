<?php

namespace App;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use SorryServiceNotAvailable;

class N2yoClient
{
    /** @var array */
    private $brightest_satellites;

    private $client;

    const TRANSACTIONS_HARD_LIMIT = 800; // 1000 is a limit from provider

    public function __construct()
    {
        $this->brightest_satellites = include(__DIR__ . '/static/brightest_satellites.php');
        $this->client               = new Client([
                                                     'base_uri' => 'https://www.n2yo.com',
                                                 ]
        );
    }

    /**
     * @param Location $location
     * @param          $apiKey
     *
     * @return VisualPass[] visual passes sorted by time
     */
    public function getVisualPasses(Location $location, $apiKey)
    {

        $requests       = [];
        $altitude       = 0;
        $days           = 10;
        $minimumSeconds = 300;

       foreach (array_slice($this->brightest_satellites, 0, 5) as $satellite) // todo debug mode
     //   foreach ($this->brightest_satellites as $satellite) // todo production mode
        {
            $uri         = "/rest/v1/satellite/visualpasses/{$satellite['id']}/{$location->getLat()}/{$location->getLong()}/$altitude/$days/$minimumSeconds?apiKey=$apiKey";
          //  echo "$uri \n";
            $request     = new Request('GET', $uri);
            $requests [] = $request;
        }

        $maxTransactionsThisMinute = 0;
        $combinedResults = [];
        $pool            = new Pool($this->client, $requests, [
                                                     'concurrency' => 20,
                                                     'fulfilled'   => function ($originalResponse, $index) use (
                                                         $apiKey, &$combinedResults, &$maxTransactionsThisMinute) {



                                                         /** @var $originalResponse Response */
                                                         $response = json_decode((string) $originalResponse->getBody(), true);

                                                     //    echo "index $index done, name = {$response['info']['satname']} \n\n";

                                                         $maxTransactionsThisMinute = max($maxTransactionsThisMinute, $response['info']['transactionscount']);

                                                         if ($maxTransactionsThisMinute > self::TRANSACTIONS_HARD_LIMIT) {
                                                             throw SorryServiceNotAvailable::becauseApiLimitReached($apiKey);
                                                         }

                                                         foreach ($response['passes'] as $pass)
                                                         {
                                                             // These satellites (or objects) are normally brighter than magnitude 4.
                                                             if ($pass['mag'] < 2) // pick up the brightest
                                                             {
                                                                 $combinedResults [] = new VisualPass(
                                                                     $response['info']['satid'],
                                                                     $response['info']['satname'],
                                                                     $pass['startUTC'],
                                                                     $pass['endUTC'],
                                                                     $pass['duration'],
                                                                     $pass['mag']
                                                                 );
                                                             }
                                                         }
                                                     },
                                                     'rejected'    => function ($reason, $index) {
                                                         throw new Exception("n2yo request failed: $reason, index = $index");
                                                     },
                                                 ]
        );

        $promise = $pool->promise();

        // force the pool of requests to complete
        $promise->wait();

     //   echo "transactions = $maxTransactionsThisMinute \n";

        usort($combinedResults, function ($a1, $a2) {

            /** @var $a1 VisualPass */
            /** @var $a2 VisualPass */

            return $a1->start_utc < $a2->start_utc;
        });

        return $combinedResults;
    }
}
