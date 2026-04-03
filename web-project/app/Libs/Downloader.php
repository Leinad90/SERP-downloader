<?php

declare(strict_types=1);

namespace App\Libs;

use App\Exception\DownloadException;
use Http\Discovery\Psr17Factory;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * @phpstan-type UrlArray array{scheme?: string, host?: string, port?: int<0,65535>, user?: string, pass?: string, query?: string, path?: string, fragment?: string}
 */

class Downloader
{
    private Cache $webCache;

    public function __construct(
        private readonly ClientInterface $Client,
        Storage $storage,
        private readonly Psr17Factory $Psr17Factory,
        private readonly string $userAgent,
    ) {
        $this->webCache = new Cache($storage, 'web');
    }

    /**
     * @param string|UrlArray $url
     * @param mixed[] $formParams
     * @param array<string, mixed> $headers
     * @throws DownloadException
     * @return string
     */
    public function download(string|array $url, array $formParams = [], array $headers = []): string
    {
        if (is_array($url)) {
            $url = $this->unparse_url($url);
        }
        $defaultHeaders = [
            'User-Agent' => $this->userAgent,
        ];
        $headers = array_merge($defaultHeaders, $headers);
        return $this->webCache->load([$url, $formParams, $headers], function () use ($url, $formParams, $headers) { /** @phpstan-ignore return.type (cache) */
            return $this->downloadNoCache($url, $formParams, $headers);
        });

    }

    /**
     * @source https://www.php.net/manual/en/function.parse-url.php#106731
     * @param UrlArray $parsed_url
     * @return string
     */
    public function unparse_url(array $parsed_url): string
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = $parsed_url['host'] ?? '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = $parsed_url['user'] ?? '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsed_url['path'] ?? '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * @param string $url
     * @param array<string, mixed> $formParams
     * @param array<string, mixed> $headers
     * @return string
     * @throws DownloadException
     */
    protected function downloadNoCache(string $url, array $formParams, array $headers): string
    {
        $request = $this->Psr17Factory->createRequest('GET', $url, [
            'headers' => $headers,
            'form_params' => $formParams,
        ]);
        try {
            $response = $this->Client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new DownloadException($e->getMessage(), $e->getCode(), $e);
        }
        return $response->getBody()->getContents();
    }

}
