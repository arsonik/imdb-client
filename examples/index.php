<?php
require '../vendor/autoload.php';

$s = new IMDb\Client();
$p = $s->personWithId('nm0001401'); // Angelina Jolie
echo json_encode($p);


$results = $s->searchMovie('Matrix');
$m = current($results);
$movie = $s->titleWithId($m['id']);
var_dump($movie);
echo json_encode($movie);


$results = $s->searchSeries('new girl');
$m = current($results);
$movie = $s->titleWithId($m['id']);
echo json_encode($movie);