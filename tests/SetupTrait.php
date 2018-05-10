<?php

namespace App\Tests;

/**
 * Trait SetupTrait
 * this trait it's created in order to have centralized all the bussiness logic about set an environment that will be
 * used repeatly by other test cases
 * @package App\Tests
 */
trait SetupTrait
{
    /**
     * setDatabase
     * if test database exist this will drop it and create a fresh one to run the tests
     */
    public function setDatabase()
    {
        system('bin/console doctrine:database:drop --if-exists --env=test --force');
        system('bin/console doctrine:database:create --env=test');
        system('bin/console doctrine:migration:migrate --env=test');
        system('bin/console doctrine:fixtures:load --env=test');
    }

    /**
     * dropDatabase
     * if test database exist this will drop it, this will be used after finish all the test case in a class
     */
    public static function dropDatabase()
    {
        system('bin/console doctrine:database:drop --if-exists --env=test --force');

    }
}