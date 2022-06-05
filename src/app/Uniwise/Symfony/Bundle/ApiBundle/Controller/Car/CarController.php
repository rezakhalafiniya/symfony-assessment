<?php

namespace Uniwise\Symfony\Bundle\ApiBundle\Controller\Car;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use Uniwise\Doctrine\Entity\Car;
use Uniwise\Symfony\Service\CarSerializer;
use Uniwise\Symfony\Service\FilterService;

/**
 * @Route("/car")
 */
class CarController extends FOSRestController {

    /**
     * @Get("")
     */
    public function getCars(ManagerRegistry $doctrine, CarSerializer $serializer) {

        $carRepo = $doctrine->getManager()->getRepository(Car::class);

        return $this->view($serializer->normilize($carRepo->findAll()));
    }


    /**
     * @Get("/filter/{filterQuery}/order/{orderBy?}")
     * @Get("/filter/{filterQuery}")
     */
    public function getFilteredAndOrderedCars(
        CarSerializer $serializer,
        FilterService $filterService,
        $filterQuery = null,
        $orderBy = null
    ) {
        $parsedFilterQuery = $filterService->parseFilterQuery($filterQuery);

        //TODO Move oder by to a separate service and make the filter return repository instead of records.
        // TODO orderBy alone should also work without filter (could also be added here to the routes and also be handled here.
        if ($orderBy){
            $orderByParams = $filterService->parseOrderBy($orderBy);
        }else{
            $orderByParams = [];
        }

        $data = $serializer->normilize($filterService->filter($parsedFilterQuery['entityClass'],$parsedFilterQuery['params'],$orderByParams));

        return $this->view($data);
    }
}
