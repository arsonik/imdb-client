IMDb-client [![Build Status](https://travis-ci.org/arsonik/imdb-client.png?branch=master)](https://travis-ci.org/arsonik/imdb-client)
===========

IMDb PHP Scrapper


Examples :

	$s = new IMDb\Client();

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