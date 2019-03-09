<?php

function curl($method, $url, array $data = []) {
    if ('GET' === $method && !empty($data)) {
        $query = http_build_query($data);
        $url .= '?' . $query;
        unset($data);
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);

    return $response;
}

function secondsToArray($seconds)
{
    $now  = new \DateTime('@0');
    $then = new \DateTime("@$seconds");

    $interval = $now->diff($then);

    $days    = $interval->format('%a');
    $hours   = $interval->format('%h');
    $minutes = $interval->format('%i');
    $seconds = $interval->format('%s');

    return [
        $days < 10 ? '0' . $days : $days,
        $hours < 10 ? '0' . $hours : $hours,
        $minutes < 10 ? '0' . $minutes : $minutes,
        $seconds < 10 ? '0' . $seconds : $seconds,
    ];
}

$secondsTillNextSatellite = $_GET['time'] ?? 4501;
$name = 'N/A';

/*
$satellites = curl('GET', 'http://spacebar.hurma.tv/satellites');
$first = array_shift($satellites);
if (!empty($first)) {
    $secondsTillNextSatellite = $first['start_utc'] - $utc;
    $name = $first['name'];
} else {
    $secondsTillNextSatellite = 0;
    $name = '';
}
*/

$countdown = secondsToArray($secondsTillNextSatellite);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Countdown</title>
    <link rel="stylesheet" href="/assets/css/countdown.css"/>
    <script src="/assets/js/jquery.countdown.js"></script>
    <script>
        $(function () {
            $('#cd').countdown({
                startTime: '<?= implode(':', $countdown) ?>'
            });
        });
    </script>
</head>
<body class="blank">
<div class="container">
    <h1>Next satellite will be seen at your location in</h1>
    <div class="cd-wrapper">
        <div id="cd"></div>
        <div class="d">
            <div>Days</div>
            <div>Hours</div>
            <div>Minutes</div>
            <div>Seconds</div>
        </div>
    </div>
    <h2>Name: <?= $name ?></h2>
</div>
</body>
</html>