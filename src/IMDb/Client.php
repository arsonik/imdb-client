<?php
namespace IMDb;

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

	protected $_timeout = 10;

	protected $_cacheResults;

	/**
	 * @var bool
	 */
	protected $_connected = false;

	protected $_sessionCookieFilePath = null;

	public function __construct($cacheResults = true){
		$this->_cacheResults = (bool) $cacheResults;
	}

	/**
	 * @param string $id
	 * @param string $passw
	 * @return bool
	 */
	public function setCredentials($id, $passw){
		$this->_sessionCookieFilePath = '/tmp/imdbsession-' . uniqid();
		$uri = 'https://secure.imdb.com/oauth/login?origurl=http://www.imdb.com/?ref_=nv_home&show_imdb_panel=1';
		$html = $this->_load($uri);

		$_ = \phpQuery::newDocument($html);
		$post = [];
		foreach($_['form[method="post"] input[name]'] as $input){
			$input = pq($input);
			$post[$input->attr('name')] = $input->val();
		}
		$post = array_merge($post, [
			'login' => $id,
			'password' => $passw
		]);

		$result = $this->_load($uri, $post);
		if(preg_match('/We have logged you in/', $result))
			$this->_connected = true;
		else
			$this->_sessionCookieFilePath = null;

		return $this->_connected;
	}

	/**
	 * @param string $titleId
	 * @param integer $rating
	 * @return bool
	 * @throws \Exception
	 */
	public function rateTitle($titleId, $rating){
		if(!$this->_connected)
			throw new \Exception('Only available when logged');
		$title = $this->titleWithId($titleId);
		if(!isset($title->ratingLinks[$rating]))
			throw new \Exception('Cannot load rating links');

		$html = $this->_load($this->_baseUri . $title->ratingLinks[$rating]);
		$result = preg_match('/Your vote of '.$rating.' was counted./', $html) > 0;
		return $result;
	}

	/**
	 * @param string $title
     * @param integer $year
	 * @return array
	 */
	public function searchMovie($title, $year = null){
		return $this->search($title, self::TYPE_MOVIE, $year);
	}

	/**
	 * @param string $title
     * @param integer $year
	 * @return array
	 */
	public function searchSeries($title, $year = null){
		return $this->search($title, self::TYPE_TV_SERIES);
	}

    /**
     * Ask google :)
     * @param string $showTitle
     * @param integer $seasonNumber
     * @param integer $episodeNumber
     * @return bool|Title
     */
    public function searchEpisode($showTitle, $seasonNumber, $episodeNumber){
        $html = $this->_load(
            'https://google.com/search?' . http_build_query([
                'q' => 'site:imdb.com "'.$showTitle.'" "tv episode" "season '.$seasonNumber.'" "episode '.$episodeNumber.'"'
            ]),
            null,
            ['mobile' => true]
        );
        if(preg_match('_imdb\.com/title/([^/]+)/_', $html, $r))
            return $this->titleWithId($r[1]);
        return false;
    }

	/**
	 * @param string $title
     * @param string $type
     * @param integer $year
	 * @return array
	 */
	public function search($title, $type, $year = null){
        $req = [
            'title' => $title,
            'title_type' => $type,
            'view' => 'simple',
        ];
        if(is_numeric($year))
            $req['release_date'] = $year . '-01-01,' . $year . '-12-31';

        $url = $this->_baseUri . '/search/title/?' . http_build_query($req);

		$html = $this->_load($url);
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
		$html = $this->_load($this->_baseUri . '/name/'.$id.'/');
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
        $result = null;
		$html = $this->_load($this->_baseUri . '/title/'.$id.'/');
		if($html){
			$_ = \phpQuery::newDocument($html);

			// Load class with title type
			$type = $_['meta[property=og:type]']->attr('content');
			$className = preg_replace_callback('/_([a-z])/', function($matches){
				return strtoupper($matches['1']);
			}, $type);
			$className = preg_replace_callback('/\.([a-z])/', function($matches){
				return '\\' . strtoupper($matches['1']);
			}, $className);

			$className = __NAMESPACE__ . '\\' . 'Title\\' . ucfirst($className);
            /** @var $title Title */
			$title = new $className();
			$title->setId($id);
            $title->assignPageContent($_);

            if($this->_connected){
                $rating = null;
                $ratesLinks = [];
                foreach($_['a[title^="Click to rate"][href^="/title/'.$id.'"]'] as $a){
                    $a = pq($a);
                    $rate = (int) $a->text();
                    $ratesLinks[$rate] = $a->attr('href');
                    if($a->hasClass('rating-your'))
                        $rating = $rate;
                }
                $title->ratingLinks = $ratesLinks;
                $title->setMyRating($rating);
            }

			$result = $title;
		}
		return $result;
	}

	/**
     * @param string $path
     * @param array $path
	 * @return mixed html string if success, bool otherwise
	 * @throws \Exception
	 */
	protected function _load($uri, $postFields = null, $params = [], $attempt = 1)
	{
        // if mobile user agent
        if(isset($params['mobile']) && $params['mobile'] === true)
            $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25';
        else
            $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.73.11 (KHTML, like Gecko) Version/7.0.1 Safari/537.73.11';

		$options = [
			CURLOPT_URL => $uri,
			CURLOPT_CONNECTTIMEOUT => $this->_timeout,
			CURLOPT_TIMEOUT => $this->_timeout,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => $ua,
			CURLOPT_HTTPHEADER => [
				'Accept-Language: en-us',
			]
		];
		if($this->_sessionCookieFilePath)
			$options += [
				CURLOPT_COOKIEFILE => $this->_sessionCookieFilePath,
				CURLOPT_COOKIEJAR => $this->_sessionCookieFilePath,
			];
        // If Post
		if(is_array($postFields))
			$options += [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($postFields)
			];
        // If Cache
        elseif($this->_cacheResults){
			$cacheFile = '/tmp/'.__METHOD__.'-'.md5(serialize($options));
			if(is_file($cacheFile))
				return include $cacheFile;
		}

        // Let's Scrap it
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
		curl_close($ch);

		if($result === false){
            switch($errno){
                case CURLE_OPERATION_TIMEOUTED:
                default:
                    throw new \Exception('Curl Error ['.$errno.'] - ' . $error, $errno);
            }
        }
		elseif($info['http_code'] >= 400 && $info['http_code'] <= 599)
			return false;
		elseif(isset($cacheFile))
			file_put_contents($cacheFile, '<?php return ' . var_export($result, true) . ';');

		return $result;
	}
}