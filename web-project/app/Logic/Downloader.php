<?php

namespace App\Logic;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\Http\Client\ClientInterface;

class Downloader
{
    private Cache $webCache;

    public function __construct(
        private readonly ClientInterface $Client,
        Storage $storage,
        private readonly string $userAgent,
    ) {
        $this->webCache = new Cache($storage,'web');
    }

    public function download(string $url, array $formParams = [], array $headers = []): \Stringable|string {
        $defaultHeaders = [
            'User-Agent' => $this->userAgent,
        ];
        $headers = array_merge($defaultHeaders, $headers);
        return $this->webCache->load($url,function () use ($url, $formParams, $headers){
            $response = $this->Client->get($url,[
                    'headers'=> $headers,
                    'form_params'=>$formParams
                ]
            );
            bdump($response);
            return $response->getBody()->getContents();
        });

    }

    /**
     * @source https://www.php.net/manual/en/function.parse-url.php#106731
     * @param array $parsed_url
     * @return string
     */
    public function unparse_url(array $parsed_url): string {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = $parsed_url['host'] ?? '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = $parsed_url['user'] ?? '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsed_url['path'] ?? '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}