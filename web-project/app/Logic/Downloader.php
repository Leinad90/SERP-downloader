<?php

namespace App\Logic;

use Nette\Caching\Cache;

trait Downloader
{
    private Cache $webCache;

    protected function download(string $url, array $params = []): \Stringable|string {
        return $this->webCache->load($url,function () use ($url, $params){
            $parsedUrl = parse_url($url);
            $response = $this->Client->get($url,[
                    'headers'=>
                        [
                            'Host'=> $parsedUrl['host'],
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
                        ]
                    ,
                    'form_params'=>$params
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
    protected function unparse_url(array $parsed_url): string {
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