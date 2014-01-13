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
		$url = 'http://akas.imdb.com/search/title/?' . http_build_query([
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
	 * @param $id
	 * @return Title
	 */
	public function titleWithId($id){
		$html = $this->_getContent('http://www.imdb.com/title/'.$id.'/');
		$_ = \phpQuery::newDocument($html);
		$title = new Title();
		$title->setId($id);
		$title->setSynopsis(trim($_['p[itemprop=description]']->text()));
		$title->setLength(new \DateInterval($_['[itemprop=duration]']->attr('datetime')));
		$title->setRating((float) $_['[itemprop=ratingValue]']->text());
		$title->setTitle(trim($_['h1 [itemprop=name]']->text()));
		$title->setVotes((int) preg_replace('/[^\d]+/', '', $_['[itemprop=ratingCount]']->text()));
		$title->setPosterUri($_['img[itemprop=image]']->attr('src'));
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

		return $title;
	}

	/**
	 * @param string $path
	 * @param int $attempt
	 * @return mixed
	 * @throws \Exception
	 */
	protected function _getContent($uri, $attempt = 1)
	{
		if($this->_cacheResults){
			$cacheFile = '/tmp/'.__METHOD__.'-'.md5($uri);
			if(is_file($cacheFile)) return include $cacheFile;
		}
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $uri,
			CURLOPT_CONNECTTIMEOUT => $this->_timeout,
			CURLOPT_TIMEOUT => $this->_timeout,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.46.3 (KHTML, like Gecko) Version/6.1 Safari/537.46.3',
			CURLOPT_HTTPHEADER => ['Accept-Language: en-us']
		]);
		$result = curl_exec($ch);
		if($result === false){
			$errno = curl_errno($ch);
			$error = curl_error($ch);

			throw new \Exception('['.$errno.'] ' . $error);
		}
		elseif($this->_cacheResults)
			file_put_contents($cacheFile, '<?php return ' . var_export($result, true) . ';');
		curl_close($ch);
		if(!$result)
			throw new \Exception('Invalid return curl_getinfo = ' . var_export(curl_getinfo($ch), true));

		return $result;
	}
} 