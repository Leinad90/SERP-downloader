<?php

declare(strict_types=1);

namespace App\Logic;

use App\DAO\googleRequestDao;
use App\DAO\PageInfo;
use App\DAO\SearchResults;
use App\Exception\ProcessSerpException;
use DOMXPath;
use GuzzleHttp\Client;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\Json;
use Tracy\Debugger;

class ProcessGoogleSerp implements ProcessSerp
{
    public string $query;

    private Cache $Cache;



    public function __construct(
        private readonly string     $url,
        Storage                     $Storage,
        private readonly ?string    $apiKey,
        private readonly Downloader $Downloader,
    ) {
        $this->Cache = new Cache($Storage, self::class);
    }


    /**
     * @return SearchResults
     * @throws ProcessSerpException
     */
    public function process(): SearchResults
    {
        $result = new SearchResults();

        $request = new googleRequestDao(
            [
                'q' => $this->query,
                'api_key' => $this->apiKey,
            ]
        );

        $data = $this->getSERP($request);
        try {
            $decoded = Json::decode($data);
        } catch (\JsonException $exception) {
            throw new ProcessSerpException("Could not parse Google request data.", 1, $exception);
        }
        assert($decoded instanceof \stdClass);
        if (!property_exists($decoded, 'organic_results') || !is_iterable($decoded->organic_results)) {
            throw new ProcessSerpException("Could not parse Google request data, organic results not found", 2);
        }
        foreach ($decoded->organic_results as $resultItem) {
            if (!property_exists($resultItem, 'link') || !property_exists($resultItem, 'title') || !property_exists($resultItem, 'snippet')) {
                throw new ProcessSerpException("Could not parse Google request data, required files not found", 3);
            }
            $result[] = new PageInfo($resultItem->link, $resultItem->title, $resultItem->snippet);
        }
        return $result;
    }

    public function getSERP(googleRequestDao $request): string
    {
        return $this->Cache->load( /** @phpstan-ignore return.type */
            $request,
            fn() => $this->getSERPnoCache($request)
        );
    }


    protected function getSERPnoCache(googleRequestDao $request): string
    {
        $request->validate();

        $params = $request->toArray();
        $headers = [];

        if ($request->useAuthorizationHeader && !empty($request->api_key)) {
            unset($params['api_key']);
            $headers['Authorization'] = 'Bearer ' . $request->api_key;
        }

        $url = parse_url($this->url);
        assert($url !== false);

        if (!empty($params)) {
            if (!array_key_exists('query', $url)) {
                $url['query'] = http_build_query($params);
            } else {
                $existingParams = [];
                parse_str($url['query'], $existingParams);
                $mergedParams = array_merge($existingParams, $params);
                $url['query'] = http_build_query($mergedParams);
            }
        }

        bdump([$url, $params, $headers]);
        $data = $this->Downloader->download($url, $params, $headers);
        return $data;
    }


    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}
