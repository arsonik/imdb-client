<?php
namespace IMDb;

class Actor extends Person {

	/** @var string */
	protected $_character;

	/**
	 * @param string $character
	 */
	public function setCharacter($character)
	{
		$this->_character = $character;
	}

	/**
	 * @return string
	 */
	public function getCharacter()
	{
		return $this->_character;
	}

	public function jsonSerialize()
	{
		return parent::jsonSerialize() + ['character' => $this->_character];
	}
} 