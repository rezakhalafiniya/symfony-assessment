<?php


namespace Uniwise\Symfony\Service;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CarSerializer
{

    public function __construct()
    {
        $this->serializer = $this->getSerializer();
    }

    /**
     * @var Serializer
     */
    private $serializer;

    public function getSerializer(){
        $encoder = new JsonEncoder();
        $noSubObjCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            $collection = new ArrayCollection();
            foreach ($innerObject as $object){
                $id = $object->getId();
                $name = $object->getName();
                $collection->add(['id' => $id , 'name' => $name]);
            }
            return $collection;
        };
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return null;
            },
            AbstractNormalizer::CALLBACKS => [
                'accessories' => $noSubObjCallback,
            ],
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        return new Serializer([$normalizer], [$encoder]);
    }

    public function serialize($data){
        return $this->serializer->serialize($data,'json');
    }

    public function normilize($data){
        return $this->serializer->normalize($data);
    }

}
