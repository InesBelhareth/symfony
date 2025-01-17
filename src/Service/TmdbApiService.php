<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    private HttpClientInterface $httpClient;
    private string $apiBaseUrl;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
       
    }
/*
    private function getUrl(string $endpoint, array $params = []): string
    {
        $queryParams = array_merge(['api_key' => "f2de0c3b19f237999b18ea3f3a73ac5e"], $params);
        $queryString = http_build_query($params);
        //return "https://api.themoviedb.org/3/{$endpoint}?api_key=f2de0c3b19f237999b18ea3f3a73ac5e&language=en-US&${params}";

        //return ""

        $url = "https://api.themoviedb.org/3/${endpoint}?api_key=your_api_key&language=en-US&page=${params}";
        error_log("Generated URL: {$url}");

        return $url;
    }
        */

    
    public function getUrl(string $endpoint, array $params = []): string
    {
        $queryString = http_build_query(array_merge(['api_key' => "f2de0c3b19f237999b18ea3f3a73ac5e", 'language' => 'en-US'], $params));
        $url = "https://api.themoviedb.org/3/{$endpoint}?{$queryString}";
        
        // Log the URL
        error_log("Generated URL: {$url}");
    
        return $url;
    }
        

    public function mediaList(string $mediaType, string $mediaCategory, int $page): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaCategory}", ['page' => $page]);
        error_log("Generated URL: {$url}");

        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaDetail(string $mediaType, int $mediaId): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaId}");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaGenres(string $mediaType): array
    {
        $url = $this->getUrl("genre/{$mediaType}/list");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaCredits(string $mediaType, int $mediaId): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaId}/credits");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaVideos(string $mediaType, int $mediaId): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaId}/videos");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaImages(string $mediaType, int $mediaId): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaId}/images");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaRecommend(string $mediaType, int $mediaId): array
    {
        $url = $this->getUrl("{$mediaType}/{$mediaId}/recommendations");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function mediaSearch(string $mediaType, string $query, int $page): array
    {
        $url = $this->getUrl("search/{$mediaType}", ['query' => $query, 'page' => $page]);
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function personDetail(int $personId): array
    {
        $url = $this->getUrl("person/{$personId}");
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function personMedias(int $personId): array
    {
        $url = $this->getUrl("person/{$personId}/combined_credits");
        return $this->httpClient->request('GET', $url)->toArray();
    }
}
