<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\RestBundle\FOSRestBundle;
use JMS\AopBundle\JMSAopBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Uniwise\Symfony\Bundle\ApiBundle\ApiBundle;

class AppKernel extends Kernel
{

    public function getRootDir()
    {
        return "/app/Uniwise/Symfony";
    }

    public function getCacheDir()
    {
        return "/var/cache/symfony/";
    }

    public function getLogDir()
    {
        return "/var/log/symfony/";
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new DoctrineBundle(),
            new FOSRestBundle(),
            new JMSSerializerBundle(),
            new SecurityBundle(),
            new ApiBundle(),
            new JMSAopBundle(),
            new DebugBundle(),
            new MakerBundle(),
        );

        return $bundles;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . "/Resources/config.yml");
    }
}
