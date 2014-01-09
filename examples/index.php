<?php
require '../vendor/autoload.php';

$s = new IMDb\Client();
$results = $s->search('Matrix');
$m = current($results);
$movie = $s->titleWithId($m['id']);
echo json_encode($movie);