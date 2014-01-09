<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/01/14
 * Time: 14:47
 */

namespace IMDb;

use IMDb\Title;

class Client {

	/**
	 * <div class="inputs">

	<table>
	<tbody><tr><td><input id="title_type-1" type="checkbox" name="title_type" value="feature"> <label for="title_type-1">Feature Film</label></td>
	<td><input id="title_type-2" type="checkbox" name="title_type" value="tv_movie"> <label for="title_type-2">TV Movie</label></td>
	<td><input id="title_type-3" type="checkbox" name="title_type" value="tv_series"> <label for="title_type-3">TV Series</label></td>
	<td><input id="title_type-4" type="checkbox" name="title_type" value="tv_episode"> <label for="title_type-4">TV Episode</label></td>
	</tr>
	<tr><td><input id="title_type-5" type="checkbox" name="title_type" value="tv_special"> <label for="title_type-5">TV Special</label></td>
	<td><input id="title_type-6" type="checkbox" name="title_type" value="mini_series"> <label for="title_type-6">Mini-Series</label></td>
	<td><input id="title_type-7" type="checkbox" name="title_type" value="documentary"> <label for="title_type-7">Documentary</label></td>
	<td><input id="title_type-8" type="checkbox" name="title_type" value="game"> <label for="title_type-8">Video Game</label></td>
	</tr>
	<tr><td><input id="title_type-9" type="checkbox" name="title_type" value="short"> <label for="title_type-9">Short Film</label></td>
	<td><input id="title_type-10" type="checkbox" name="title_type" value="video"> <label for="title_type-10">Video</label></td>
	<td><input id="title_type-11" type="checkbox" name="title_type" value="unknown"> <label for="title_type-11">Unknown Work</label></td>
	</tr></tbody></table>
	</div>
	 */
	const MOVIE = 'feature';
	const TV_SHOW = 'tv_series';
	const TV_EPISODE= 'tv_episode';

	protected $_timeout = 4;

	public function search($title, $type = self::MOVIE){
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
	 * @throws Exception
	 */
	protected function _getContent($uri, $attempt = 1)
	{
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

			throw new Exception('['.$errno.'] ' . $error);
		}
		curl_close($ch);
		if(!$result)
			throw new Exception('Invalid return curl_getinfo = ' . var_export(curl_getinfo($ch), true));

		return $result;
	}
} 