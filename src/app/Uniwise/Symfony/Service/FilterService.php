<?php

namespace Uniwise\Symfony\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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
     * @param array $orderBy
     * @return array
     * @throws BadParamException
     */
    public function filter(string $entityClass, array $params,array $orderBy = []): array
    {
        $validatedParams = $this->validateParams($entityClass, $params);
        if ($orderBy){
            $validatedOrderByParams = $this->validateParams($entityClass, $orderBy);
        }else{
            $validatedOrderByParams = [];
        }
        /** @var ServiceEntityRepository $entityRepo */
        $entityRepo = $this->entityManager->getRepository($entityClass);
        if ($validatedParams['relationParams'] ){
            $filteredResult = $this->getFromJoinQuery($entityRepo,$validatedParams,$validatedOrderByParams);
        }else{
            if ($validatedOrderByParams){
                $entityRepo = $this->addOrderBy($validatedOrderByParams,$entityRepo);
            }
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
            }else{
                $ownParams[$property] = $value;
            }

            if (!method_exists($entityClass, $getterName)) {
                throw new BadParamException();
            }
        }
        return [
            "ownParams" => $ownParams,
            "relationParams" => $relationParams,
        ];
    }

    private function getFromJoinQuery(
        ServiceEntityRepository $entityRepo,
        array $validatedParams,
        array $validatedOrderByParams = []
    ) {

        $entityRepo = $entityRepo->createQueryBuilder('e');
        foreach ($validatedParams['relationParams'] as $relation => $columnValue) {
            $entityRepo = $entityRepo->join('e.' . $relation, 'r')
                ->where('r.' . $columnValue['column'] . '=:' . $columnValue['column'])
                ->setParameter($columnValue['column'], $columnValue['value']);
        }
        if ($validatedOrderByParams) {
            $entityRepo = $this->addOrderBy($validatedOrderByParams, $entityRepo);
        }
        return $entityRepo->getQuery()->getResult();
    }

    public function parseFilterQuery(string $query){
        $arrayQuery = str_getcsv($query,';');
        if (count($arrayQuery) !== 2){
            throw new BadParamException();
        }
        $entityClassDotNotation = $arrayQuery[0];

        $entityClassName = str_replace('.','\\',$entityClassDotNotation);

        if(!class_exists($entityClassName)){
            throw new BadParamException();
        }

        $filterParams = str_getcsv($arrayQuery[1]);
        $parsedFilterParams = [];
        foreach ($filterParams as $paramValue){
            $paramValueArray = explode('=',$paramValue);
            $parsedFilterParams[$paramValueArray[0]] = $paramValueArray[1];
        }

        return [
            'entityClass' => $entityClassName,
            'params' => $parsedFilterParams
        ];

    }

    public function parseOrderBy(string $orderBy)
    {
        $filterParams = str_getcsv($orderBy);
        $parsedOrderByParams = [];
        foreach ($filterParams as $paramValue){
            $paramValueArray = explode('=',$paramValue);
            $parsedOrderByParams[$paramValueArray[0]] = $paramValueArray[1];
        }

        return $parsedOrderByParams;
    }

    /**
     * @param array $validatedOrderByParams
     * @param QueryBuilder|ServiceEntityRepository $entityRepo
     * @return QueryBuilder
     */
    protected function addOrderBy(array $validatedOrderByParams, QueryBuilder $entityRepo): QueryBuilder
    {
        foreach ($validatedOrderByParams['relationParams'] as $columnValue) {
            $entityRepo->orderBy('r.' . $columnValue['column'], $columnValue['value']);
        }
        foreach ($validatedOrderByParams['ownParams'] as $column=> $value) {
            $entityRepo->orderBy('e.' . $column, $value);
        }
        return $entityRepo;
    }

}
