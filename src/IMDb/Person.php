<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/01/14
 * Time: 16:00
 */

namespace IMDb;


class Person implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_name;

	/**
	 * @var string
	 */
	protected $_bio;

	/**
	 * @var \DateTime
	 */
	protected $_birthDate;

	/**
	 * @var string
	 */
	protected $_posterUri;

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
	 * @param string $bio
	 */
	public function setBio($bio)
	{
		$this->_bio = $bio;
	}

	/**
	 * @return string
	 */
	public function getBio()
	{
		return $this->_bio;
	}

	/**
	 * @param \DateTime $birthDate
	 */
	public function setBirthDate($birthDate)
	{
		$this->_birthDate = $birthDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getBirthDate()
	{
		return $this->_birthDate;
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
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	public function jsonSerialize()
	{
		return [
			'id' => $this->_id,
			'name' => $this->_name,
			'bio' => $this->_bio,
			'birthDate' => $this->_birthDate instanceof \DateTime ? $this->_birthDate->format('Y-m-d') : null,
			'poster' => $this->_posterUri,
		];
	}
}