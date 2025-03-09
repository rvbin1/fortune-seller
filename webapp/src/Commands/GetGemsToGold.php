<?php

namespace App\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'update:prices')]
class GetSellPricesCommand extends Command
{
    private const COPPER = 'copper';
    private const SILVER = 'silver';
    private const GOLD = 'gold';
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private HttpClientInterface $client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Ruft die Verkaufsdaten aus der API von GW2 und gibt diese aus.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $url = "https://api.guildwars2.com/v2/commerce/exchange/coins?quantity=1000000";

        $response = $this->client->request('GET', $url);
        $content = $response->getContent();

        $results = json_decode($content, true);
        if (isset($results["quantity"])) {
            $gemAmount = $results["quantity"];
        }

        # hier müsste halt noch hin was du dann mit dieser Zahl machne willst. Du bekommst halt nur eine Zahl zurück, welche dir sagt wie viele gems to für 100 Gold bekommst

        return Command::SUCCESS;
    }
}
