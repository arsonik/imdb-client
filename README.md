imdb-client [![Build Status](https://travis-ci.org/arsonik/imdb-client.png?branch=master)](https://travis-ci.org/arsonik/imdb-client)
===========

IMDb PHP Scrapper, psr-0

Usage
=====
```php
<?php
$s = IMDb\Client::iniWithConfig(include __DIR__ . '/config.sample.php');;

$results = $s->searchMovie('Matrix');
$m = current($results);
$movie = $s->titleWithId($m['id']);
var_dump($movie);
echo json_encode($movie);

$results = $s->searchSeries('new girl');
$m = current($results);
$movie = $s->titleWithId($m['id']);
var_dump($movie);
echo json_encode($movie);
```

See ./tests/ directory for more examples