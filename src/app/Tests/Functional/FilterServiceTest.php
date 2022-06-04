<?php

namespace Tests\Functional;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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

        $this->assertEquals($filteredEntities, $filteredEntitiesActual);
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
