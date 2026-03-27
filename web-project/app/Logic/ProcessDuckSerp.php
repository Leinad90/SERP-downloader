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
    public string $query {
        set {
            $this->query = $value;
        }
    }

    private Cache $Cache;


    public function __construct(
        private readonly string     $url,
        Storage                     $Storage,
        private readonly Downloader $Downloader,
    )
    {
        $this->Cache = new Cache($Storage, self::class);
    }

    public function process(): SearchResults
    {
        return $this->Cache->load(
            $this->query,
            function () {
                return $this->processNoCache($this->query);
            }
        );
    }


    protected function processNoCache(string $query): SearchResults {
        $result = new SearchResults();
        $data = $this->getSerp($query);
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

    protected function getSerp(string $query): string|\Stringable
    {
        $urlParts = parse_url($this->url);
        $params = ['q' => $query];
        $urlParts['query'] = http_build_query($params);
        $url = $this->Downloader->unparse_url($urlParts);
        $headers = [
                            'Host'=> $urlParts['host'],
                            'User-Agent' => 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:148.0) Gecko/20100101 Firefox/148.0',
                            'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language'=>'cs,sk;q=0.9,en-US;q=0.8,en;q=0.7',
                            'Accept-Encoding'=>'gzip, deflate, br, zstd',
                            'DNT'=>'1',
                            'Connection'=>'keep-alive',
                            'Upgrade-Insecure-Requests'=>1,
                            'Sec-Fetch-Dest'=>'document',
                            'Sec-Fetch-Mode'=>'navigate',
                            'Sec-Fetch-Site'=>'none',
                            'Sec-Fetch-User'=>'?1',
                            'Priority'=>'u=0, i',
            ];
        return $this->Downloader->download($url, $headers);
    }




}