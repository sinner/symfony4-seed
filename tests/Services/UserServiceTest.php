<?php

namespace App\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\DataFixtures\AppUsersFixtures;
use App\Tests\SetupTrait;

class UserServiceTest extends WebTestCase
{
    use SetupTrait;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Set the test environment foreach test case that will run this class
     */
    protected function setUp()
    {
        parent::setUp();
        $client = static::createClient();
        $this->userService = $client->getContainer('App\Services\Entity\UserService');
        $this->setDatabase();
    }

    public function testSomething()
    {
        $this->assertTrue(true);
    }


    public static function tearDownAfterClass()
    {
        UserServiceTest::dropDatabase();
    }
}
