<?php
namespace IMDb\Title\Video;

use IMDb\Title;

class Episode extends Title {

    /**
     * @var string
     */
    protected $_tvShowId;

    /**
     * @var string
     */
    protected $_tvShowName;

    /**
     * @var integer
     */
    protected $_seasonNumber;

    /**
     * @var integer
     */
    protected $_episodeNumber;

    public function assignPageContent(\phpQueryObject $_){
        parent::assignPageContent($_);

        preg_match('/Season (\d+), Episode (\d+)/', $_['h2.tv_header']->text(), $r);
        $this->_seasonNumber = $r[1];
        $this->_episodeNumber = $r[2];

        $this->_tvShowName = trim($_['h2.tv_header a']->text());
        preg_match('_/(tt[^/]+)/_', $_['h2.tv_header a']->attr('href'), $r);
        $this->_tvShowId = $r[1];

        $this->_datePublished = \DateTime::createFromFormat('(d M. Y)', $_['h1.header .nobr']->text());
    }

    /**
     * @param int $episodeNumber
     */
    public function setEpisodeNumber($episodeNumber)
    {
        $this->_episodeNumber = $episodeNumber;
    }

    /**
     * @return int
     */
    public function getEpisodeNumber()
    {
        return $this->_episodeNumber;
    }

    /**
     * @param int $seasonNumber
     */
    public function setSeasonNumber($seasonNumber)
    {
        $this->_seasonNumber = $seasonNumber;
    }

    /**
     * @return int
     */
    public function getSeasonNumber()
    {
        return $this->_seasonNumber;
    }

    /**
     * @param string $tvShowId
     */
    public function setTvShowId($tvShowId)
    {
        $this->_tvShowId = $tvShowId;
    }

    /**
     * @return string
     */
    public function getTvShowId()
    {
        return $this->_tvShowId;
    }

    /**
     * @param string $tvShowName
     */
    public function setTvShowName($tvShowName)
    {
        $this->_tvShowName = $tvShowName;
    }

    /**
     * @return string
     */
    public function getTvShowName()
    {
        return $this->_tvShowName;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'tvShowId' => $this->_tvShowId,
            'tvShowName' => $this->_tvShowName,
            'seasonNumber' => $this->_seasonNumber,
            'episodeNumber' => $this->_episodeNumber,
        ];
    }
} 