<?php

namespace Uniwise\Symfony\Bundle\ApiBundle\Controller\Car;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use Uniwise\Doctrine\Entity\Car;
use Uniwise\Symfony\Service\CarSerializer;

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
}
