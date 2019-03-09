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

    $utc_time = $request->getQueryParam('utc_time');

    if (! $utc_time)
    {
        $response = $response->withStatus(400);
        $response->getBody()->write('required utc_time parameter missing');

        return $response;
    }
    $lat  = $request->getQueryParam('lat');
    $long = $request->getQueryParam('long');

    if ($lat && $long)
    {
        $location = new Location($lat, $long);
    } else
    {
        /** @var GeoLocator $geoLocator */
        $geoLocator = $container->get(GeoLocator::class);
        $location   = $geoLocator->locate($request->getServerParam('REMOTE_ADDR'));
    }

    /** @var N2yoClient $client */
    $client = $container->get(N2yoClient::class);
    echo json_encode($client->getVisualPasses($location, getenv('SYSTEM_API_KEY')));


    $response->getBody()->write("done <br>");

    return $response;
}
);

$app->run();

