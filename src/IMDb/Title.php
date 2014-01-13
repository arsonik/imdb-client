<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/01/14
 * Time: 15:29
 */

namespace IMDb;


class Title implements \JsonSerializable {
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
	protected $_directors;

	/**
	 * @var array
	 */
	protected $_cast;

	/**
	 * @var \DateTime
	 */
	protected $_datePublished;

	/**
	 * @param array $cast
	 */
	public function setCast($cast)
	{
		$this->_cast = $cast;
	}

	/**
	 * @return array
	 */
	public function getCast()
	{
		return $this->_cast;
	}

	/**
	 * @param array $directors
	 */
	public function setDirectors($directors)
	{
		$this->_directors = $directors;
	}

	/**
	 * @return array
	 */
	public function getDirectors()
	{
		return $this->_directors;
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
			'cast' => $this->_cast,
			'directors' => $this->_directors,
			'length' => $this->_length->format('%i'),
		];
	}
}