<?php

use App\GeoLocator;
use App\Location;
use App\N2yoClient;
use App\Repository;
use Slim\App;
use Slim\Container;
use Slim\Http\Request as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';

$container = new Container();
$container->get('settings')->replace(['displayErrorDetails' => true]);
$app       = new App($container);

$env = new Dotenv();
$env->load(__DIR__ . '/../.env');

$container[GeoLocator::class] = function ($container) {
    return new GeoLocator();
};

$container[Repository::class] = function ($container) {
    $host     = getenv('DB_HOST');
    $dbname   = getenv('DB_NAME');
    $login    = getenv('DB_LOGIN');
    $password = getenv('DB_PASSWORD');

    return new Repository($host, $dbname, $login, $password);
};

$container[N2yoClient::class] = function ($container) {
    return new N2yoClient();
};

$app->get('/satellites', function (Request $request, Response $response) use ($container) {

    $utc_time = $request->getQueryParam('utc_time', (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp());

    $ip = $request->getQueryParam('forced_ip', $request->getServerParam('REMOTE_ADDR'));

    $lat  = $request->getQueryParam('lat');
    $long = $request->getQueryParam('long');

    if ($lat && $long)
    {
        $location = new Location($lat, $long);
    } else
    {
        /** @var GeoLocator $geoLocator */
        $geoLocator = $container->get(GeoLocator::class);
        $location   = $geoLocator->locate($ip);
    }

    $response = $response->withHeader('Content-Type', 'application/json');

     /** @var Repository $repo */
    $repo = $container->get(Repository::class);

    // try cache
    $cachedVisualPass = $repo->getClosestVisualPassByIP($utc_time, $ip);
    if ($cachedVisualPass) {
        $response->getBody()->write(json_encode($cachedVisualPass));
        $response = $response->withHeader('X-VisualPass-Cache', 'hit');
        $response = $response->withHeader('X-Cache-Method', 'by IP');

        return $response;
    }

    $cachedVisualPass = $repo->getClosestVisualPassByRoundedLatLong($utc_time, $location->getRoundedLat(), $location->getRoundedLong());
    if ($cachedVisualPass) {
        $response->getBody()->write(json_encode($cachedVisualPass));
        $response = $response->withHeader('X-VisualPass-Cache', 'hit');
        $response = $response->withHeader('X-Cache-Method', 'by rounded Lat/Long');

        return $response;
    }

    if ($location->getName())
    {
        $cachedVisualPass = $repo->getClosestVisualPassByCity($location->getName(), $ip);
        if ($cachedVisualPass)
        {
            $response->getBody()->write(json_encode($cachedVisualPass));
            $response = $response->withHeader('X-VisualPass-Cache', 'hit');
            $response = $response->withHeader('X-Cache-Method', 'by city');

            return $response;
        }
    }




    /** @var N2yoClient $client */
    $client = $container->get(N2yoClient::class);

    $response = $response->withHeader('X-VisualPass-Cache', 'Miss');
    $visualPasses = $client->getVisualPasses($location, getenv('SYSTEM_API_KEY'));

    $repo->insertVisualPasses($visualPasses, $location, $ip);

    $response->getBody()->write(json_encode($visualPasses[0]));
    return $response;
}
);

$app->run();

