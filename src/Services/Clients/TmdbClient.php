<?php

namespace App\Services\Clients;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class TmdbClient
{
    private const BASE_URL = "https://api.themoviedb.org/3/";
    private Client $client;
    private string $authToken;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->authToken = $_SERVER["TMDB_AUTH_TOKEN"];
        $this->logger = $logger;
    }

    private function get(string $query): ResponseInterface|null
    {
        try
        {
            return $this->client->get(self::BASE_URL . $query,
                [
                    'headers' => [
                        'Authorization' => $this->authToken,
                        'accept' => 'application/json',
                    ],
                ]);
        }
        catch(\Exception $e)
        {
            return null;
        }
    }

    public function findPerson(string $fullName, array $movies): \stdClass|null
    {
        $fullName = $this->encodeString($fullName);

        $currentPage = 1;
        do
        {
            $response = $this->get("search/person?query=$fullName&include_adult=false&language=en-US&page=$currentPage");
            if(!$response || $response->getStatusCode() != Response::HTTP_OK)
                return null;

            $data = json_decode($response->getBody()->getContents());

            if($data->total_results == 1)
                return $data->results[0];

            $currentPage = $data->page + 1;
            $totalPages = $data->total_pages;

            foreach ($data->results as $person)
            {
                foreach($person->known_for as $knownFor)
                {
                    if($knownFor->media_type == "tv")
                    {
                        if(in_array($knownFor->original_name, $movies))
                            return $person;
                    }else
                    {
                        if(in_array($knownFor->original_title, $movies))
                            return $person;
                    }
                }
            }
        }while($currentPage <= $totalPages);

        return null;
    }

    public function searchMovies(string $searchQuery): array
    {
        $query = $this->encodeString($searchQuery);
        $response = $this->get("search/movie?query=$query&include_adult=false&language=en-US&page=1");
        if(!$response || $response->getStatusCode() != Response::HTTP_OK)
            return [];

        $data = json_decode($response->getBody()->getContents());
        if($data->total_results > 0)
            return $data->results;
        return [];
    }

    public function findMulti(string $title, \DateTimeInterface $releaseDate = null): \stdClass|null
    {
        $title = $this->encodeString($title);
        $apiPath = "search/multi?query=$title&include_adult=false&language=en-US&page=1";
        if($releaseDate)
        {
            $year = $releaseDate->format("Y");
            $apiPath .= "&year=$year";
        }

        $response = $this->get($apiPath);
        if(!$response || $response->getStatusCode() != Response::HTTP_OK)
            return null;

        $data = json_decode($response->getBody()->getContents());
        if($data->total_results > 0)
            return $data->results[0];
        return null;
    }

    public function findMovieVideos(string $title, \DateTimeInterface $releaseDate = null): array
    {
        $title = $this->encodeString($title);
        $apiPath = "search/multi?query=$title&include_adult=false&language=en-US&page=1";
        if($releaseDate)
        {
            $year = $releaseDate->format("Y");
            $apiPath .= "&year=$year";
        }

        $response = $this->get($apiPath);
        if(!$response || $response->getStatusCode() != Response::HTTP_OK)
            return [];

        $data = json_decode($response->getBody()->getContents());
        if($data->total_results == 0)
            return [];

        $movieData = $data->results[0];
        $response = $this->get("$movieData->media_type/$movieData->id/videos?language=en-US");
        if(!$response || $response->getStatusCode() != Response::HTTP_OK)
            return [];

        $videosData = json_decode($response->getBody()->getContents());
        if(count($videosData->results) == 0)
            return [];

        return $videosData->results;
    }

    private function getCredits(string $tmdbId): \stdClass|null
    {
        $response = $this->get("movie/$tmdbId/credits?language=en-US");
        if(!$response || $response->getStatusCode() != Response::HTTP_OK)
            return null;

        return json_decode($response->getBody()->getContents());
    }

    public function getDirector(string $tmdbId): \stdClass|null
    {
        $credits = $this->getCredits($tmdbId);
        if(!$credits) return null;

        if(count($credits->crew) == 0)
            return null;

        foreach($credits->crew as $crewMember)
        {
            if($crewMember->job === "Director")
                return $crewMember;
        }
        return null;
    }

    public function getCast(string $tmdbId): array
    {
        $credits = $this->getCredits($tmdbId);
        if(!$credits) return [];

        return $credits->cast;
    }

    private function encodeString(string $s): string
    {
        $s = urlencode($s);
        return str_replace('+', '%20', $s);
    }
}