<?php

namespace Tests\Application;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Uniwise\Doctrine\Entity\Car;
use Uniwise\Symfony\Service\CarSerializer;

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
     * @var object
     */
    private $serializer;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->serializer = $kernel->getContainer()
            ->get(CarSerializer::class);

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
    public function api_returns_json()
    {
        $this->client->request('GET','/car');

        $this->assertJson($this->client->getResponse()->getContent(), 'Car API didn\'t return Json');

    }

    /**
     * @test
     */
    public function api_returns_all_cars()
    {
        $cars = $this->entityManager->getRepository(Car::class)->findAll();
        $serializedCars = $this->serializer->serialize($cars);
        $this->client->request('GET','/car');
        $this->assertEquals($serializedCars,$this->client->getResponse()->getContent(),'Returned Json doesn\'t match DB');
    }
}
