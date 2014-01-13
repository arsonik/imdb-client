<?php
namespace IMDb;

use IMDb\Title;

class Client {

	const TYPE_MOVIE = 'feature';
	const TYPE_TV_SERIES = 'tv_series';
	const TYPE_TV_EPISODE= 'tv_episode';
	const TYPE_TV_MOVIE = 'tv_movie';
	const TYPE_TV_SPECIAL = 'tv_special';
	const TYPE_MINI_SERIES = 'mini_series';
	const TYPE_DOCUMENTARY = 'documentary';
	const TYPE_GAME = 'game';
	const TYPE_SHORT_FILM = 'short';
	const TYPE_VIDEO = 'video';
	const TYPE_UNKNOWN = 'unknown';

	protected $_baseUri = 'http://akas.imdb.com';

	protected $_timeout = 5;

	protected $_cacheResults;

	public function __construct($cacheResults = true){
		$this->_cacheResults = (bool) $cacheResults;
	}

	/**
	 * @param string $title
	 * @return array
	 */
	public function searchMovie($title){
		return $this->search($title, self::TYPE_MOVIE);
	}

	/**
	 * @param string $title
	 * @return array
	 */
	public function searchSeries($title){
		return $this->search($title, self::TYPE_TV_SERIES);
	}

	/**
	 * @param $title
	 * @param $type
	 * @return array
	 */
	public function search($title, $type){
		$url = $this->_baseUri . '/search/title/?' . http_build_query([
			'title' => $title,
			'title_type' => $type,
			'view' => 'simple',
		]);
		$html = $this->_getContent($url);
		$_ = \phpQuery::newDocument($html);

		$results = [];
		foreach($_['table.results tr td.title a[href^=/title/tt]'] as $td){
			$td = pq($td);
			$results[] = [
				'title' => $td->text(),
				'id' => preg_replace('@^.*(tt\d+).*@', '$1', $td->attr('href')),
			];
		}
		return $results;
	}

	/**
	 * @param string $id
	 * @return Person
	 */
	public function personWithId($id){
		$result = false;
		$html = $this->_getContent($this->_baseUri . '/name/'.$id.'/');
		if($html){
			$_ = \phpQuery::newDocument($html);
			$person = new Person();
			$person->setId($id);
			$bio = $_['[itemprop=description]'];
			$bio->find('span.see-more')->remove();
			$person->setBio(trim($bio->text()));
			$person->setName(trim($_['h1 [itemprop=name]']->text()));
			$person->setBirthDate(\DateTime::createFromFormat('Y-m-d', $_['time[itemprop=birthDate]']->attr('datetime')));
			$person->setPosterUri($_['img[itemprop=image]']->attr('src'));
			$result = $person;
		}
		return $result;
	}

	/**
	 * @param $id
	 * @return Title
	 */
	public function titleWithId($id){
		$result = false;
		$html = $this->_getContent($this->_baseUri . '/title/'.$id.'/');
		if($html){
			$_ = \phpQuery::newDocument($html);
			$title = new Title();
			$title->setId($id);
			$title->setSynopsis(trim($_['p[itemprop=description]']->text()));
			$title->setLength(new \DateInterval($_['[itemprop=duration]']->attr('datetime')));
			$title->setRating((float) $_['[itemprop=ratingValue]']->text());
			$title->setTitle(trim($_['h1 [itemprop=name]']->text()));
			$title->setVotes((int) preg_replace('/[^\d]+/', '', $_['[itemprop=ratingCount]']->text()));
			$title->setPosterUri($_['img[itemprop=image]']->attr('src'));
			$title->setDatePublished(\DateTime::createFromFormat('Y-m-d', $_['.infobar [itemprop="datePublished"]']->attr('content')));
			$g = [];
			foreach($_['.infobar [itemprop=genre]'] as $genre)
				$g[] = pq($genre)->text();
			$title->setGenres($g);
			// Directors
			$c = [];
			foreach($_['div[itemprop=director] a[itemprop=url]'] as $p){
				$p = pq($p);
				$actor = new Person();
				$actor->setId(preg_replace('@^.*(nm\d+).*@', '$1', $p->attr('href')));
				$actor->setName($p->text());
				$c[] = $actor;
			}
			$title->setDirectors($c);
			// Casting
			$c = [];
			foreach($_['table.cast_list tr:has([itemprop=name])'] as $p){
				$p = pq($p);
				$actor = new Actor();
				$actor->setId(preg_replace('@^.*(nm\d+).*@', '$1', $p['[itemprop=url]']->attr('href')));
				$actor->setName($p['[itemprop=name]']->text());
				$actor->setCharacter($p['a[href^=/character]']->text());
				$c[] = $actor;
			}
			$title->setCast($c);

			$result = $title;
		}
		return $result;
	}

	/**
	 * @param string $path
	 * @param int $attempt
	 * @return mixed html string if success, bool otherwise
	 * @throws \Exception
	 */
	protected function _getContent($uri, $attempt = 1)
	{
		$options = [
			CURLOPT_URL => $uri,
			CURLOPT_CONNECTTIMEOUT => $this->_timeout,
			CURLOPT_TIMEOUT => $this->_timeout,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.73.11 (KHTML, like Gecko) Version/7.0.1 Safari/537.73.11',
			CURLOPT_HTTPHEADER => [
				'Accept-Language: en-us',
			]
		];
		if($this->_cacheResults){
			$cacheFile = '/tmp/'.__METHOD__.'-'.md5(serialize($uri));
			if(is_file($cacheFile))
				return include $cacheFile;
		}
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($result === false){
			throw new \Exception('['.curl_errno($ch).'] ' . curl_error($ch));
		}
		elseif($info['http_code'] >= 400 && $info['http_code'] <= 599){
			return false;
		}
		elseif($this->_cacheResults)
			file_put_contents($cacheFile, '<?php return ' . var_export($result, true) . ';');

		return $result;
	}
} 