<?php

namespace App\Logic;

use App\DAO\googleRequestDao;
use App\DAO\PageInfo;
use App\DAO\SearchResults;
use DOMXPath;
use GuzzleHttp\Client;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\Json;
use Tracy\Debugger;

class ProcessGoogleSerp implements ProcessSerp
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
        private readonly ?string    $apiKey,
        private readonly Downloader $Downloader,
    )
    {
        $this->Cache = new Cache($Storage, self::class);
    }


    public function process(): SearchResults {
        $result = new SearchResults();

        $request = new googleRequestDao(
            [
                'q'=>$this->query,
                'api_key'=>$this->apiKey
            ]
        );

        $data = $this->getSERP($request);
        $decoded = Json::decode($data);
        Debugger::log($decoded);
        foreach ($decoded->organic_results as $resultItem) {
            $result[] = new PageInfo($resultItem->link, $resultItem->title, $resultItem->snippet);
        }
        return $result;
    }


    function getSERP(googleRequestDao $request): string
    {
        $request->validate();

        $params = $request->toArray();
        $headers = [];

        if ($request->useAuthorizationHeader && !empty($request->api_key)) {
            unset($params['api_key']);
            $headers['Authorization'] = 'Bearer ' . $request->api_key;
        }

        $url = $this->url;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }


        $data = $this->Downloader->download($url,$params,$headers);
        return $data;
    }




}