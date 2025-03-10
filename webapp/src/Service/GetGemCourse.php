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

        return $results["quantity"] ?? 0;
    }
}