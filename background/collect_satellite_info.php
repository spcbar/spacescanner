<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


require __DIR__ . '/../vendor/autoload.php';

// echo "start\n";

// https://www.heavens-above.com/SatInfo.aspx?satid=22626&lat=0&lng=0&loc=Unspecified&alt=0&tz=UCT


$brightest_satellites = include(__DIR__ . '/../app/static/brightest_satellites.php');

$idToSatelliteInfo = array_column($brightest_satellites, null, 'id');

$client               = new Client([
                                                     'base_uri' => 'https://www.heavens-above.com/',
                                                 ]
        );



foreach ($brightest_satellites as $info)
{
    $id = $info['id'];

  //  echo "processing $id\n";

    $url = "/SatInfo.aspx?satid=$id&lat=0&lng=0&loc=Unspecified&alt=0&tz=UCT";

    $request = new Request('GET', $url);

    $response = $client->send($request);

    if ($response->getStatusCode() === 200)
    {
     //   echo "request successful\n";
        $body = (string) $response->getBody();

        $dom = SimpleHtmlDom\str_get_html($body);

        foreach ($dom->find('tr') as $tr)
        {
            $tds = [];
            foreach ($tr->find('td') as $td) {
                $tds[]= $td;
            }

            if (stripos($tds[0]->plaintext, 'country') !== false)
            {
                $idToSatelliteInfo[$id]['origin'] = trim($tds[1]->plaintext);
            }

            if (stripos($tds[0]->plaintext, 'UTC') !== false)
            {
                $idToSatelliteInfo[$id]['launch_date'] = trim($tds[1]->plaintext);
            }

            if (stripos($tds[0]->plaintext, 'Launch site') !== false)
            {
                $idToSatelliteInfo[$id]['launch_site'] = trim($tds[1]->plaintext);
            }
        }

        $dom->clear();
        unset($dom);
        unset($tr);
        unset($td, $tds);

    } else {
        throw new Exception("error response from third-party service ((");
    }
}

echo var_export($idToSatelliteInfo, true);


// echo "done\n";
