<?php
require '../vendor/autoload.php';

$s = new IMDb\Client();
$results = $s->searchMovie(isset($_GET['q']) ? $_GET['q'] : 'Matrix');
$m = current($results);
$movie = $s->titleWithId($m['id']);
echo json_encode($movie);


$results = $s->searchSeries('new girl');
$m = current($results);
$movie = $s->titleWithId($m['id']);
echo json_encode($movie);