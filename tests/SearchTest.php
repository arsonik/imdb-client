<?php
class SearchTest extends PHPUnit_Framework_TestCase {

	protected $_imdbClient;


	public function __construct()
	{
		$this->_imdbClient = new IMDb\Client();
	}

	public function testSearchMovie(){

		$s =
		// search movie
		$results = $this->_imdbClient->searchMovie('Matrix');
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) > 0);
		$m = current($results);

		// movie is matrix ?
		$this->assertEquals($m['id'], 'tt0133093');
	}

	public function testLoadTitleWithId(){

		// search movie
		$results = $this->_imdbClient->searchMovie('Matrix');
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) > 0);
		$m = current($results);

		// load movie
		$movie = $this->_imdbClient->titleWithId('tt0133093');
		$this->assertInstanceOf('\IMDb\Title', $movie);
		$this->assertEquals($movie->getId(), 'tt0133093');
	}
} 