<?php
namespace IMDb;

abstract class Title implements \JsonSerializable {
	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_title;

	/**
	 * @var string
	 */
	protected $_synopsis;

	/**
	 * @var float
	 */
	protected $_rating;

	/**
	 * @var integer
	 */
	protected $_votes;

	/**
	 * @var \DateInterval
	 */
	protected $_length;

	/**
	 * @var array
	 */
	protected $_genres;

	/**
	 * @var string
	 */
	protected $_posterUri;

	/**
	 * @var array
	 */
	protected $_people = [];


	/**
	 * @var \DateTime
	 */
	protected $_datePublished;

	/**
	 * @var integer
	 */
	protected $_myRating;

	public $ratingLinks = [];

	/**
	 * @param int $myRating
	 */
	public function setMyRating($myRating)
	{
		$this->_myRating = $myRating;
	}

	/**
	 * @return int
	 */
	public function getMyRating()
	{
		return $this->_myRating;
	}

	/**
	 * @return array
	 */
	public function getCast()
	{
		return $this->_people['actors'];
	}

	/**
	 * @return array
	 */
	public function getDirectors()
	{
        return $this->_people['director'];
	}

	/**
	 * @param \DateTime $datePublished
	 */
	public function setDatePublished($datePublished)
	{
		$this->_datePublished = $datePublished;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatePublished()
	{
		return $this->_datePublished;
	}

	/**
	 * @param string $posterUri
	 */
	public function setPosterUri($posterUri)
	{
		$this->_posterUri = $posterUri;
	}

	/**
	 * @return string
	 */
	public function getPosterUri()
	{
		return $this->_posterUri;
	}





	/**
	 * @param array $genres
	 */
	public function setGenres($genres)
	{
		$this->_genres = $genres;
	}

	/**
	 * @return array
	 */
	public function getGenres()
	{
		return $this->_genres;
	}

	/**
	 * @param float $rating
	 */
	public function setRating($rating)
	{
		$this->_rating = $rating;
	}

	/**
	 * @return float
	 */
	public function getRating()
	{
		return $this->_rating;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @param \DateInterval $length
	 */
	public function setLength(\DateInterval $length)
	{
		$this->_length = $length;
	}

	/**
	 * @return \DateInterval
	 */
	public function getLength()
	{
		return $this->_length;
	}

	/**
	 * @param string $synopsis
	 */
	public function setSynopsis($synopsis)
	{
		$this->_synopsis = $synopsis;
	}

	/**
	 * @return string
	 */
	public function getSynopsis()
	{
		return $this->_synopsis;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->_title;
	}

	/**
	 * @param int $votes
	 */
	public function setVotes($votes)
	{
		$this->_votes = $votes;
	}

	/**
	 * @return int
	 */
	public function getVotes()
	{
		return $this->_votes;
	}


    public function assignPageContent(\phpQueryObject $_){
        $this->setSynopsis(trim($_['p[itemprop=description]']->text()));
        if($duration = $_['[itemprop=duration]']->attr('datetime'))
            $this->setLength(new \DateInterval($duration));
        $this->setRating((float) $_['[itemprop=ratingValue]']->text());
        $this->setTitle(trim($_['h1 [itemprop=name]']->text()));
        $this->setVotes((int) preg_replace('/[^\d]+/', '', $_['[itemprop=ratingCount]']->text()));
        $this->setPosterUri($_['img[itemprop=image]']->attr('src'));
        $this->setDatePublished(\DateTime::createFromFormat('Y-m-d', $_['.infobar [itemprop="datePublished"]']->attr('content')));

        $g = [];
        foreach($_['.infobar [itemprop=genre]'] as $genre)
            $g[] = pq($genre)->text();
        $this->setGenres($g);

        foreach($_['#title-overview-widget [itemtype="http://schema.org/Person"][itemprop!=actors]'] as $x){
            $x = pq($x);
            foreach($x['a[itemprop=url]'] as $p){
                $p = pq($p);
                $person = new Person();
                $person->setId(preg_replace('@^.*(nm\d+).*@', '$1', $p->attr('href')));
                $person->setName($p->text());
                if(!isset($this->_people[$x->attr('itemprop')]))
                    $this->_people[$x->attr('itemprop')] = [];
                $this->_people[$x->attr('itemprop')][$person->getId()] = $person;
            }
        }

        // Casting
        if(!isset($this->_people['actors']))
            $this->_people['actors'] = [];
        foreach($_['table.cast_list tr:has([itemprop=name])'] as $p){
            $p = pq($p);
            $actor = new Actor();
            $actor->setId(preg_replace('@^.*(nm\d+).*@', '$1', $p['[itemprop=url]']->attr('href')));
            $actor->setName($p['[itemprop=name]']->text());
            $actor->setCharacter($p['a[href^=/character]']->text());
            $this->_people['actors'][$actor->getId()] = $actor;
        }
    }

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'id' => $this->_id,
			'title' => $this->_title,
			'votes' => $this->_votes,
			'rating' => $this->_rating,
			'synopsis' => $this->_synopsis,
			'poster' => $this->_posterUri,
			'genres' => $this->_genres,
			'people' => $this->_people,
			'length' => $this->_length->format('%i'),
		];
	}
}