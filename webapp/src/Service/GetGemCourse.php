<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetGemCourse
{
    private const string URL = "https://api.guildwars2.com/v2/commerce/exchange/coins?quantity=1000000";

    public function __construct(private readonly HttpClientInterface $client) {}

    public function getGemCourse():int
    {
        $response = $this->client->request('GET', self::URL);
        $content = $response->getContent();

        $results = json_decode($content, true);
        if (!is_array($results)) return 0;
        if (!array_key_exists('quantity', $results)) return 0;
        return (int)$results["quantity"];
    }
}