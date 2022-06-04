<?php

namespace Uniwise\Symfony\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Uniwise\Doctrine\Entity\Accessory;
use Uniwise\Symfony\Exceptions\BadParamException;

class FilterService
{

    /**
     * FilterService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param string $entityClass
     * @param array $params
     * @return array
     */
    public function filter(string $entityClass, array $params): array
    {
        $validatedParams = $this->validateParams($entityClass, $params);

        /** @var ServiceEntityRepository $entityRepo */
        $entityRepo = $this->entityManager->getRepository($entityClass);
        if ($validatedParams['relationParams']){
            $filteredResult = $this->getFromJoinQuery($entityRepo,$validatedParams);
        }else{
            $filteredResult = $entityRepo->findBy($validatedParams['ownParams']);
        }

        return $filteredResult;
    }

    protected function validateParams($entityClass, $params)
    {
        $ownParams = [];
        $relationParams = [];

        foreach ($params as $property => $value) {
            $getterName = 'get' . ucfirst($property);
            if (strpos($property, 'rel.') === 0) {
                $relationParamName = str_replace('rel.','',$property);
                $relationParamName = str_getcsv($relationParamName,'|');
                $getterName = 'get' . ucfirst($relationParamName[0]);
                $relationParams[$relationParamName[0]] = ['column'=>$relationParamName[1],'value'=>$value];
            }
            if (method_exists($entityClass, $getterName)) {
                $ownParams[$property] = $value;
            } else {
                throw new BadParamException();
            }
        }
        return [
            "ownParams" => $ownParams,
            "relationParams" => $relationParams,
        ];
    }

    private function getFromJoinQuery(ServiceEntityRepository $entityRepo, array $validatedParams)
    {
        $entityRepo = $entityRepo->createQueryBuilder('e');
        foreach ($validatedParams['relationParams'] as $relation => $columnValue){
            $entityRepo = $entityRepo->join('e.accessories','r')
                ->where('r.'.$columnValue['column'].'=:'.$columnValue['column'])
            ->setParameter($columnValue['column'],$columnValue['value']);
        }
        $s = $entityRepo->getQuery()->getSQL();
        $p = $entityRepo->getQuery()->getParameters();
        return $entityRepo->getQuery()->getResult();
    }

}
