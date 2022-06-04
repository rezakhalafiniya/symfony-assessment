<?php
namespace Uniwise\Symfony\Service;

use Doctrine\ORM\EntityManagerInterface;

class FilterService {

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
    public function filter(string $entityClass,array $params): array
    {

        $validatedParams = $this->validateParams($entityClass, $params);

        $entityRepo = $this->entityManager->getRepository($entityClass);
        $filteredResult = $entityRepo->findBy($validatedParams['acceptedParams']);

        return $filteredResult;
    }

    protected function validateParams($entityClass, $params)
    {
        $acceptedParams = [];
        $badParams = [];
        foreach ($params as $property => $value){
            $getterName = 'get'.ucfirst($property);
            if (method_exists($entityClass,$getterName)){
                $acceptedParams[$property] = $value;
            }else{
                $badParams[$property] = $value;
            }
        }
        return ['acceptedParams' => $acceptedParams, 'badParrams' => $badParams];
    }
}
