<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use \Doctrine\Common\Persistence\ManagerRegistry;

/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var \Doctrine\ORM\Tools\SchemaTool
     */
    private $schemaTool;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata[]
     */
    private $classes;

    /**
     * @var Response|null
     */
    private $response;

    public function __construct(KernelInterface $kernel, ManagerRegistry $doctrine)
    {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @BeforeScenario
     */
    public function createSchema()
    {
        system('bin/console doctrine:database:drop --if-exists --env=test --force --no-interaction --no-debug --quiet');
        system('bin/console doctrine:database:create --env=test --no-interaction --no-debug --quiet');
        system('bin/console doctrine:migration:migrate --env=test --no-interaction --no-debug --quiet');
    }

}
