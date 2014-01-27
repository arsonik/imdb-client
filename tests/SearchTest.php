<?php
class SearchTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var IMDb\Client
	 */
	protected $_imdbClient;

	public function __construct()
	{
		$this->_imdbClient = IMDb\Client::iniWithConfig(include __DIR__ . '/../config.sample.php');
	}

	public function testCredentials(){
		// $this->assertFalse($this->_imdbClient->setCredentials('user@domain.com', 'xxx'));
		// $this->_imdbClient->rateTitle('tt0133093', 2);
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
		$this->assertInstanceOf('\IMDb\Title\Video\Movie', $movie);
		$this->assertEquals($movie->getId(), 'tt0133093', 'Found movie doesnt match exepected id');
		$this->assertEquals($movie->getTitle(), 'The Matrix', 'Found movie doesnt match exepected title');
		$this->assertEquals($movie->getDatePublished()->format('Y-m-d'), '1999-03-31', 'Found movie doesnt match exepected publised date');
        $this->assertNotEmpty(json_encode($movie));
	}

    public function testSearchEpisode(){
        if(isset($this->_imdbClient->getGoogleCustomSearchConfig()['apiKey'])){
            /** @var $movie IMDb\Title\Video\Episode */
            $ep = $this->_imdbClient->searchEpisode('the big bang theory', 3, 6);
            $this->assertEquals($ep->getTitle(), 'The Cornhusker Vortex');

            // as of Jan 21st 2014, no duration for this episode
            $ep = $this->_imdbClient->searchEpisode('the following', 2, 1);
            $this->assertEquals($ep->getTitle(), 'Resurrection');
        }
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

    public function testLoadSeries(){
        $movie = $this->_imdbClient->titleWithId('tt1826940');
        $this->assertInstanceOf('\IMDb\Title\Video\TvShow', $movie);
        $this->assertEquals($movie->getTitle(), 'New Girl');
    }

    public function testLoadEpisode(){
        /** @var $movie IMDb\Title\Video\Episode */
        $movie = $this->_imdbClient->titleWithId('tt2301469');
        $this->assertInstanceOf('\IMDb\Title\Video\Episode', $movie);
        $this->assertEquals($movie->getTvShowName(), 'Breaking Bad');
        $this->assertEquals($movie->getTitle(), 'Gliding Over All');
        $this->assertEquals($movie->getSeasonNumber(), 5);
        $this->assertEquals($movie->getEpisodeNumber(), 8);
    }

	public function testLoadPersonWithId(){
		$person = $this->_imdbClient->personWithId('nm0001401');
		$this->assertInstanceOf('\IMDb\Person', $person);
		$this->assertEquals($person->getId(), 'nm0001401', 'Found person doesnt match exepected id');
		$this->assertEquals($person->getName(), 'Angelina Jolie', 'Found person doesnt match exepected title');
	}
} 