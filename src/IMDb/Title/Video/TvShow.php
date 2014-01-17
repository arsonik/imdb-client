<?php
namespace IMDb\Title\Video;

use IMDb\Title;


class TvShow extends Title
{
    protected $_seasons = [];

    public function assignPageContent(\phpQueryObject $_){
        parent::assignPageContent($_);
        $this->_seasons = [];
        foreach($_['a[href^="/title/'.$this->_id.'/episodes?season="]'] as $seasonLink){
            $this->_seasons[] = (int) pq($seasonLink)->text();
        }
    }
} 