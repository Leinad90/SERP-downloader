<?php

namespace App\Logic;

use App\DAO\PageInfo;
use App\DAO\SearchResults;
use DOMXPath;
use GuzzleHttp\Client;
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
        $xpath = new DOMXPath($DOM);
        $xmlResulsts = $xpath->query("//div[contains(@class,'web-result')]");
        foreach ($xmlResulsts as $xmlResulst) {
            $titleXml = $xpath->query(".//h2", $xmlResulst);
            $title = $titleXml[0]?->textContent;
            bdump($xmlResulst->childNodes);
            $spinnetXml = $xpath->query(".//a[@class='result__snippet']", $xmlResulst);
            bdump($spinnetXml);
            $spinnet = $spinnetXml[0]?->textContent;
            $url = $spinnetXml[0]?->getAttribute("href");
            $result[] = new PageInfo($url,$title,$spinnet);
        }
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