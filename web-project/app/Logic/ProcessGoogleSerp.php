<?php

declare(strict_types=1);

namespace App\Logic;

use App\DTO\googleRequestDto;
use App\DTO\PageInfo;
use App\DTO\SearchResults;
use App\Exception\DownloadException;
use App\Exception\ProcessSerpException;
use App\Libs\Downloader;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;

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
     * @throws DownloadException
     */
    public function process(): SearchResults
    {

        $request = new googleRequestDto(
            [
                'q' => $this->query,
                'api_key' => $this->apiKey,
            ]
        );

        return $this->Cache->load( /* @phpstan-ignore return.type  */
            $request,
            fn() => $this->realProcess($request)
        );
    }

    /**
     * @return SearchResults
     * @throws ProcessSerpException
     * @throws DownloadException
     */
    public function realProcess(googleRequestDto $request): SearchResults
    {
        $result = new SearchResults();
        $data = $this->getSERP($request);
        $decoded = $this->validateSearchResponse($data);
        foreach ($decoded->organic_results as $resultItem) {
            $result[] = new PageInfo($resultItem->link, $resultItem->title, $resultItem->snippet);
        }
        return $result;
    }

    /**
     * @throws ProcessSerpException
     */
    private function validateSearchResponse(string $data): stdClass
    {
        try {
            $decoded = Json::decode($data);
            if (!$decoded instanceof stdClass) {
                throw new ProcessSerpException('Google response must be an object.');
            }
        } catch (JsonException $e) {
            throw new ProcessSerpException('Google response is not valid JSON: ' . $e->getMessage(), __LINE__, $e);
        }

        if (!property_exists($decoded, 'organic_results')) {
            throw new ProcessSerpException('Google response does not contain organic_results.', 3);
        }

        if (!is_array($decoded->organic_results)) {
            throw new ProcessSerpException('Google response organic_results must be an array.', 4);
        }

        foreach ($decoded->organic_results as $index => $resultItem) {
            $this->validateOrganicResult($resultItem, $index);
        }
        return $decoded;
    }

    /**
     * @throws ProcessSerpException
     */
    private function validateOrganicResult(mixed $resultItem, int $index): void
    {
        if (!$resultItem instanceof stdClass) {
            throw new ProcessSerpException(sprintf('organic_results[%d] must be an object.', $index), 5);
        }

        $this->requireInt($resultItem, 'position', $index);
        $this->requireString($resultItem, 'title', $index);
        $this->requireString($resultItem, 'link', $index);
        $this->requireString($resultItem, 'snippet', $index);

    }



    /**
     * @throws ProcessSerpException
     */
    private function requireString(stdClass $object, string $property, int $index): string
    {
        if (!property_exists($object, $property) || !is_string($object->{$property}) || $object->{$property} === '') {
            throw new ProcessSerpException(
                sprintf('organic_results[%d].%s must be a non-empty string.', $index, $property),
                8
            );
        }

        return $object->{$property};
    }

    /**
     * @throws ProcessSerpException
     */
    private function requireInt(stdClass $object, string $property, int $index): int
    {
        if (!property_exists($object, $property) || !is_int($object->{$property})) {
            throw new ProcessSerpException(
                sprintf('organic_results[%d].%s must be an integer.', $index, $property),
                9
            );
        }

        return $object->{$property};
    }


    /**
     * @throws DownloadException
     */
    protected function getSERP(googleRequestDto $request): string
    {
        $request->validate();

        $params = $request->toArray();
        $headers = [];

        if ($request->useAuthorizationHeader && !empty($request->api_key)) {
            unset($params['api_key']);
            $headers['Authorization'] = 'Bearer ' . $request->api_key;
        }

        return $this->Downloader->download($this->url, $params, $headers);
    }


    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}
