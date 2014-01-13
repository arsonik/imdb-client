<?php
class SearchTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var IMDb\Client
	 */
	protected $_imdbClient;

	public function __construct()
	{
		$this->_imdbClient = new IMDb\Client();
	}

	public function testSearchMovie(){

		// search movie
		$results = $this->_imdbClient->searchMovie('Matrix');
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) > 0);
		$m = current($results);

		// movie is matrix ?
		$this->assertEquals($m['id'], 'tt0133093');
	}

	public function testLoadTitleWithId(){
		$movie = $this->_imdbClient->titleWithId('tt0133093');
		$this->assertInstanceOf('\IMDb\Title', $movie);
		$this->assertEquals($movie->getId(), 'tt0133093', 'Found movie doesnt match exepected id');
		$this->assertEquals($movie->getTitle(), 'The Matrix', 'Found movie doesnt match exepected title');
		$this->assertEquals($movie->getDatePublished()->format('Y-m-d'), '1999-03-31', 'Found movie doesnt match exepected publised date');
	}

	public function testSearchSeries(){

		// search serie
		$results = $this->_imdbClient->searchSeries('new girl');
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) > 0);
		$m = current($results);
		// serie is new girl ?
		$this->assertEquals($m['id'], 'tt1826940', 'Found series doesnt match exepected id');
	}

	public function testLoadPersonWithId(){
		$person = $this->_imdbClient->personWithId('nm0001401');
		$this->assertInstanceOf('\IMDb\Person', $person);
		$this->assertEquals($person->getId(), 'nm0001401', 'Found person doesnt match exepected id');
		$this->assertEquals($person->getName(), 'Angelina Jolie', 'Found person doesnt match exepected title');
	}
} 