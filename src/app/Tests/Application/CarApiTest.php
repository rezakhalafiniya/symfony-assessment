<?php

namespace Tests\Application;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Uniwise\Doctrine\Entity\Car;
use Uniwise\Symfony\Service\CarSerializer;
use Uniwise\Symfony\Service\FilterService;

class CarApiTest extends WebTestCase
{

    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;
    /**
     * @var CarSerializer
     */
    private $serializer;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get('doctrine')
            ->getManager();

        $this->serializer = $container->get(CarSerializer::class);

        $this->filterService = $container->get(FilterService::class);

        $this->client = static::createClient();

    }

    public function tearDown(): void
    {

        $this->entityManager = null;
    }

    /**
     * @test
     */
    public function api_has_car_endpoint()
    {
        $this->client->request('GET','/car');

        $this->assertNotEquals(404,$this->client->getResponse()->getStatusCode(),'Car Endpoint is not defined');
    }

    /**
     * @test
     */
    public function car_api_endpoint_returns_json()
    {
        $this->client->request('GET','/car');

        $this->assertJson($this->client->getResponse()->getContent(), 'Car API didn\'t return Json');

    }

    /**
     * @test
     */
    public function car_api_endpoint_returns_all_cars()
    {
        $cars = $this->entityManager->getRepository(Car::class)->findAll();
        $serializedCars = $this->serializer->serialize($cars);
        $this->client->request('GET','/car');
        $this->assertEquals($serializedCars,$this->client->getResponse()->getContent(),'Returned Json doesn\'t match DB');
    }

    /**
     * @test
     */
    public function car_api_endpoint_returns_filtered_cars()
    {
        $entityDotNotationName = str_replace('\\','.',Car::class);
        $filterParams = [
            'brand' => 'BMW',
            'rel.accessories|name' => 'gps'
        ];
        $filterQuery = $entityDotNotationName.';'.http_build_query($filterParams,'',',');

        $cars = $this->filterService->filter(Car::class,$filterParams);
        $serializedCars = $this->serializer->serialize($cars);

        $this->client->request('GET','/car/filter/'.$filterQuery);

        $this->assertEquals($serializedCars,$this->client->getResponse()->getContent(),'Returned Json doesn\'t match DB');
    }
}
