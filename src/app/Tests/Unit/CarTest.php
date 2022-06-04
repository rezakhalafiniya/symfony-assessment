<?php
namespace Tests\Unit;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Uniwise\Doctrine\Entity\Accessory;
use Uniwise\Doctrine\Entity\Car;

class CarTest extends TestCase
{

    /**
     * @var Car
     */
    private $car;

    public function setUp(): void
    {
        $this->car  = new Car();
    }

    /**
     * @test
     */
    public function car_sets_and_gets_brand()
    {
        $this->car->setBrand('BMW');
        $this->assertEquals('BMW',$this->car->getBrand(),'Brand is not set and get correctly');
    }

    /**
     * @test
     */
    public function car_sets_and_gets_model()
    {
        $this->car->setModel('M3');
        $this->assertEquals('M3',$this->car->getModel(),'Model is not set and get correctly');

    }

    /**
     * @test
     */
    public function car_sets_and_gets_color()
    {
        $this->car->setColor('Black');
        $this->assertEquals('Black',$this->car->getColor(),'Color is not set and get correctly');
    }

    /**
     * @test
     */
    public function car_sets_and_gets_gas_economy()
    {
        $this->car->setGasEconomy('Petrol');
        $this->assertEquals('Petrol',$this->car->getGasEconomy(),'GasEconomy is not set and get correctly');
    }

    /**
     * @test
     */
    public function car_sets_and_gets_accessory()
    {
        $accessory = new Accessory();
        $this->car->addAccessory($accessory);
        $this->assertEquals([$accessory],$this->car->getAccessories()->toArray(),'Accessory is not set and get correctly');
    }
}
