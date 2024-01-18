<?php

namespace App\Command;

use ApiPlatform\Metadata\HttpOperation;
use App\Entity\Scooter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;


#[AsCommand(
    name: self::NAME,
    description: 'Simulate a single scooter client'
)]
class SimulateSingleClientCommand extends Command
{
    public const NAME = 'app:simulate-single-client';
    protected const BASE_URL = 'http://127.0.0.1:8000%s';

    public function __construct(protected HttpClientInterface $httpClient, protected RouterInterface $router, protected string $apiKey)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $initialLocation = ['latitude' => 1.200000, 'longitude' => 50.32323];

        $firstAvailableScooterId = $this->sendTraceableRequest(
            'get_scooters_in_rectangle_area',
            HttpOperation::METHOD_GET,
            $output,
            [
                'query' => [
                    'southWestLocation' => '1.4215, 1.6972',
                    'northEastLocation' => '85.4215, 75.6972',
                    'status' => Scooter::SCOOTER_STATUS_AVAILABLE
                ]
            ]
        )
            ->toArray()['hydra:member'][0]['id'] ?? [0]['id'];

        $tripId = $this->sendTraceableRequest('start_trip', HttpOperation::METHOD_POST, $output, ['json' => ['scooterId' => $firstAvailableScooterId]])->toArray()['id'];

        $travelTime = rand(10, 15);
        $startTime = time();

        while (time() - $startTime < $travelTime) {
            sleep(3);
            $this->sendTraceableRequest('update_scooter', HttpOperation::METHOD_PATCH, $output, ['json' => ['location' => $this->addSomeValueToGeolocation($initialLocation), 'status' => Scooter::SCOOTER_STATUS_AVAILABLE]], ['id' => $firstAvailableScooterId]);
        }

        $this->sendTraceableRequest('end_trip', HttpOperation::METHOD_PATCH, $output, ['json' => ['location' => $this->addSomeValueToGeolocation($initialLocation)]], ['id' => $tripId]);

        sleep(rand(2, 5));

        return Command::SUCCESS;
    }


    private function sendTraceableRequest(string $routeName, string $httpMethod, OutputInterface $output, array $options = [], array $routeParameters = []): ResponseInterface
    {
        $relativeUrl = $this->router->generate($routeName, $routeParameters);
        $absoluteUrl = sprintf(self::BASE_URL, $relativeUrl);

        $specificOptions = [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->apiKey),
                'Content-Type' => match ($httpMethod) {
                    HttpOperation::METHOD_PATCH => 'application/merge-patch+json',
                    default => 'application/json'
                }
            ],
            'query' => $options['query'] ?? [],
            'json' => $options['json'] ?? [],
        ];

        $output->writeln(sprintf(PHP_EOL . "Sending <fg=blue>%s %s</> request to %s", $routeName, $httpMethod, $absoluteUrl));
        $output->writeln('query: ' . json_encode($specificOptions['query']));
        $output->writeln('json: ' . json_encode($specificOptions['json']));

        $response = $this->httpClient->request($httpMethod, $absoluteUrl, $specificOptions);

        $statusCode = $response->getStatusCode();
        $statusMessage = "Response Status: " . $statusCode;

        if ($statusCode >= 200 && $statusCode < 300) {
            $output->writeln(sprintf("<info>%s</info>", $statusMessage));
        } else {
            $output->writeln(sprintf("<error>%s</error>", $statusMessage));
        }
        $output->writeln(PHP_EOL . "Response Content: " . $response->getContent(false));

        return $response;
    }

    private function addSomeValueToGeolocation(array $location): array
    {
        $newLatitude = $location['latitude'] + (rand(1, 5) * 0.001);
        $newLongitude = $location['longitude'] + (rand(1, 5) * 0.001);

        return ['latitude' => $newLatitude, 'longitude' => $newLongitude];
    }
}
