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
    protected $client;
    public function __construct()
    {
        parent::__construct();
        $this->client = static::createClient();
        $this->userService = $this->client->getContainer()->get('test.user.service');
    }

    /**
     * Set the test environment foreach test case that will run this class
     */
    protected function setUp()
    {
        parent::setUp();
        $this->setDatabase();
    }

    /**
     * @covers \App\Services\Entity\UserService::findAll
     *
     */
    public function testFindAllUser()
    {
        $users = $this->userService->findAll();
        $this->assertNotEmpty($users);

    }


    public static function tearDownAfterClass()
    {
        UserServiceTest::dropDatabase();
    }
}
