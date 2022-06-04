<?php

namespace Tests\Functional;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Uniwise\Symfony\Exceptions\BadParamException;
use Uniwise\Doctrine\Entity\Accessory;
use Uniwise\Doctrine\Entity\Car;
use Uniwise\Symfony\Service\FilterService;

class FilterServiceTest extends KernelTestCase
{

    /**
     * @var FilterService
     */
    private $filterService;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->entityManager->getConnection()->beginTransaction();

        $this->filterService = new FilterService($this->entityManager);
    }

    public function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager = null;
    }

    /**
     * @test
     */
    public function it_filters_entity_based_on_param()
    {
        $entity = Car::class;
        $params = [
            'brand' => 'BMW'
        ];
        $accessory = new Accessory();
        $accessory->setName('gps');
        $carParams = [
            'brand' => 'BMW',
            'color' => 'blue',
            'model' => 'M3',
            'gasEconomy' => 'pertrol',
            'rel.accessory' => $accessory,
        ];
        $filteredEntities = $this->createEntityArray(Car::class, $carParams, 3);

        $filteredEntitiesActual = $this->filterService->filter($entity, $params);

        $this->assertEquals($filteredEntities, $filteredEntitiesActual,'Filtering based on params didn\'t work');
    }

    /**
     * @test
     */
    public function it_returns_empty_when_params_do_not_match()
    {
        $entity = Car::class;
        $params = [
            'brand' => 'BMW',
            'model' => 'M1'
        ];
        $accessory = new Accessory();
        $accessory->setName('gps');
        $carParams = [
            'brand' => 'BMW',
            'color' => 'blue',
            'model' => 'M3',
            'gasEconomy' => 'pertrol',
            'rel.accessory' => $accessory,
        ];
        $this->createEntityArray(Car::class, $carParams, 3);

        $filteredEntitiesActual = $this->filterService->filter($entity, $params);

        $this->assertEquals([], $filteredEntitiesActual,'Filtering based on params didn\'t work');
    }

    /**
     * @test
     */
    public function it_throws_Exception_when_bad_params_are_given()
    {
        $this->expectException(BadParamException::class);

        $entity = Car::class;
        $params = [
            'abrand' => 'BMW'
        ];

        $this->filterService->filter($entity, $params);
    }

    /**
     * @test
     */
    public function it_filters_entity_based_on_relationship()
    {
        $entity = Car::class;
        $params = [
            'rel.accessories|name' => 'gps',
            'brand' => 'BMW'
        ];
        $accessory = new Accessory();
        $accessory->setName('gps');
        $accessory2 = new Accessory();
        $accessory2->setName('radio');
        $carParams = [
            'brand' => 'BMW',
            'color' => 'blue',
            'model' => 'M3',
            'gasEconomy' =>'pertrol',
            'rel.accessory' => $accessory,
        ];
        $carParams2 = [
            'brand' => 'aaBMW',
            'color' => 'blue',
            'model' => 'M3',
            'gasEconomy' =>'pertrol',
            'rel.accessory' => $accessory2,
        ];
        $carParams3 = [
            'brand' => 'BMW',
            'color' => 'blue',
            'model' => 'M3',
            'gasEconomy' =>'pertrol',
            'rel.accessory' => $accessory,
        ];
        $filteredEntities = $this->createEntityArray(Car::class,$carParams);
        // Adding more to check if the filter is only returning the filtered cars
        $this->createEntityArray(Car::class,$carParams2);
        $this->createEntityArray(Car::class,$carParams3);

        $filteredEntitiesActual = $this->filterService->filter($entity, $params);

        $this->assertEquals($filteredEntities, $filteredEntitiesActual,'Filtering based on relationship didn\'t work');
    }

    /**
     * @param string $entityClass
     * @param array $params
     * @param int $count
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createEntityArray(string $entityClass, array $params, int $count = 3)
    {
        $entityArray = [];
        for ($i = 0; $i < $count; $i++) {
            $entity = new $entityClass();
            foreach ($params as $property => $value) {
                if (strpos($property, 'rel.') === 0) {
                    $methodName = 'add' . ucfirst(str_replace('rel.', '', $property));
                    $this->entityManager->merge($value);
                    $this->entityManager->persist($value);
                } else {
                    $methodName = 'set' . ucfirst($property);
                }

                $entity->{$methodName}($value);
            }
            $entityArray[] = $entity;
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        return $entityArray;
    }

}
