<?php

namespace Tests\Unit;

use Tests\Unit\Mock\MockEntity;
use Tests\Unit\Mock\MockRepository;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Uniwise\Symfony\Service\FilterService;

class FilterServiceTest extends TestCase
{

    /**
     * @var FilterService
     */
    private $filterService;

    public function setUp(): void
    {
        $this->filterService = new FilterService();
    }

    /**
     * @test
     */
    public function it_filters_entity_based_on_param(){
        $entityRepo = MockRepository::class;
        $params = [
            'mockParam' => 'test'
        ];
        $filteredEntities = [
            new MockEntity(),
            new MockEntity(),
            new MockEntity(),
        ];

        $filteredEntitiesActual = $this->filterService->filter(new $entityRepo(), $params);

        $this->assertEquals($filteredEntities,$filteredEntitiesActual);
    }

}
