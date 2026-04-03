<?php

declare(strict_types=1);

namespace App\Logic;

use App\DTO\PageInfo;
use App\DTO\SearchResults;
use App\Exception\DownloadException;
use App\Exception\ProcessSerpException;
use App\Libs\Downloader;
use DOMXPath;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

/**
 * @phpstan-import-type UrlArray from Downloader
 */

class ProcessDuckSerp implements ProcessSerp
{
    public string $query;

    private Cache $Cache;

    /** @var UrlArray $urlParts */
    private array $urlParts;


    public function __construct(
        private readonly string     $url,
        Storage                     $Storage,
        private readonly Downloader $Downloader,
    ) {
        $this->Cache = new Cache($Storage, self::class);
        $urlParts = parse_url($this->url);
        if ($urlParts === false) {
            throw new \InvalidArgumentException('Invalid URL');
        }
        $this->urlParts = $urlParts;
    }

    public function process(): SearchResults
    {
        return $this->Cache->load( /* @phpstan-ignore return.type  */
            $this->query,
            fn() => $this->processNoCache($this->query)
        );
    }


    /**
     * @throws ProcessSerpException
     * @throws DownloadException
     */
    protected function processNoCache(string $query): SearchResults
    {
        $result = new SearchResults();
        $data = $this->getSerp($query);
        libxml_use_internal_errors(true);
        $DOM = new \DOMDocument();
        $DOM->loadHTML($data);
        $xpath = new DOMXPath($DOM);
        $xmlResulsts = $xpath->query("//div[contains(@class,'web-result')]");
        assert($xmlResulsts !== false);
        foreach ($xmlResulsts as $xmlResult) {
            assert($xmlResult instanceof \DOMNode);
            $titleXml = $xpath->query(".//h2", $xmlResult);
            assert($titleXml !== false);
            $title = $titleXml[0]?->textContent;
            $spinnetXml = $xpath->query(".//a[@class='result__snippet']", $xmlResult);
            assert($spinnetXml !== false);
            $spinnet = $spinnetXml[0]?->textContent;
            $url = $spinnetXml[0]?->getAttribute("href");
            if ($url === null || $title === null || $spinnet === null) {
                throw new ProcessSerpException("No data found");
            }
            $result[] = new PageInfo($url, $title, $spinnet);
        }
        return $result;
    }

    /**
     * @throws DownloadException
     */
    protected function getSerp(string $query): string
    {
        $urlParts = $this->urlParts;
        $params = ['q' => $query];
        $urlParts['query'] = http_build_query($params);
        $url = $this->Downloader->unparse_url($urlParts);
        $headers = [
            'User-Agent' => 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'cs,sk;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding' => 'gzip, deflate, br, zstd',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => 1,
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Priority' => 'u=0, i',
        ];
        return $this->Downloader->download($url, $headers);
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }


}
