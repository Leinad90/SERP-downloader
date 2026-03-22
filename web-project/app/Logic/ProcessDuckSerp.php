<?php

namespace App\Logic;

use App\DAO\SearchResults;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

class ProcessDuckSerp implements ProcessSerp
{

    use Downloader;

    public string $query {
        set {
            $this->query = $value;
        }
    }


    public function __construct(
        private string $url,
        Storage $Storage,
        private Client $Client,
    )
    {
        $this->webCache = new Cache($Storage, 'web');
    }


    public function process(): SearchResults {
        $result = new SearchResults();
        $data = $this->getSerp();
        libxml_use_internal_errors(true);
        $DOM = new \DOMDocument();
        $DOM->loadHTML($data);
        echo $data;
        bdump($data);
        $xpath = new DOMXPath($DOM);
        $a = $xpath->query("//div[contains(@class,'web-result')]");
        bdump($a);
        $a = $xpath->query("//div");
        bdump($a);
        return $result;
    }

    protected function getSerp(): string|\Stringable
    {
        $urlParts = parse_url($this->url);
        $params = ['q' => $this->query];
        $urlParts['query'] = http_build_query($params);
        $url = $this->unparse_url($urlParts);
        return $this->download($url);
    }




}