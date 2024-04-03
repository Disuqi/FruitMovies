<?php

namespace App\Services\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class OsmClient
{
    private const BASE_URL = "https://overpass-api.de/api/interpreter";

    private Client $client;
    private LoggerInterface $logger;

    function __construct(LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->logger = $logger;
    }

    /**
     * @throws GuzzleException
     */
    private function post(string $query): ResponseInterface|null
    {
        return $this->client->post(self::BASE_URL, ["body" => $query]);
    }

    public function findCinemas(float $lat, float $long, float $range): array
    {
        $body = "[out:json];
                    (
                        node[\"amenity\"=\"cinema\"](around:$range,$lat,$long);
                    );
                    out body;
                    >;
                    out skel qt;";


        $response = $this->post($body);
        if($response->getStatusCode() != Response::HTTP_OK)
            throw new \Error("Something went wrong");

        $data = json_decode($response->getBody()->getContents());
        return $data->elements;
    }
}