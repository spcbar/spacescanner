<?php

use App\GeoLocator;
use App\Location;
use Slim\App;
use Slim\Container;
use Slim\Http\Request as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$container = new Container();
$app       = new App($container);

$container[GeoLocator::class] = function ($container) {
    return new GeoLocator();
};



$app->get('/satellites', function (Request $request, Response $response) use ($container) {

    $utc_time = $request->getQueryParam('utc_time');

    if (!$utc_time) {
        $response = $response->withStatus(400);
        $response->getBody()->write('required utc_time parameter missing');
        return $response;
    }
    $lat      = $request->getQueryParam('lat');
    $long     = $request->getQueryParam('long');

    if ($lat && $long)
    {
        $location = new Location($lat, $long);

    } else
    {
        /** @var GeoLocator $geoLocator */
        $geoLocator = $container->get(GeoLocator::class);
        $location = $geoLocator->locate($request->getServerParam('REMOTE_ADDR'));
    }

    $response->getBody()->write("hello world, location = $location");

    return $response;
}
);

$app->run();

