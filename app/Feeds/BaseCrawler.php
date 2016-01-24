<?php

namespace App\Feeds;

class BaseCrawler
{
	/**
	 * Defines the url(s) to crawl
	 *
	 * @var string|array
	 */
	public $url;

	/**
	 * The guzzle client
	 *
	 * @var Client
	 */
	protected $client;

	public function __construct()
	{
		$this->client = new \GuzzleHttp\Client;
	}

	public function run()
	{
		$urls = is_array($this->url) ? $this->url : [$this->url];

		foreach ($urls as $url) {
			$response = $this->client->request('GET', $url,[
				'headers' => [
					'User-Agent' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
				],
			]);
			$contents[] = (string) $response->getBody();
		}

		return $contents;
	}
}