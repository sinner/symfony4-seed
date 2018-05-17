<?php

namespace App\Tests\Services;

use App\Entity\User;
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
     * Find all the users registered at the database
     * @covers \App\Services\Entity\UserService::findAll
     * @group user management
     */
    public function testFindAllUser()
    {
        $users = $this->userService->findAll();
        $this->assertNotEmpty($users);
    }

    /**
     * Search for an user that has a specific username
     * @covers \App\Services\Entity\UserService::findBy
     * @group user management
     */
    public function testFindAnUserByExistingUsername()
    {
        $users = $this->userService->findBy(['username' => 'cory']);
        $this->assertNotEmpty($users);
        $this->assertCount(1, $users);
        $this->assertTrue(is_array($users));
        $this->assertInstanceOf(User::class, $users[0]);
    }

    /**
     * Search for an user that his username does not exists
     * @covers \App\Services\Entity\UserService::findBy
     * @group user management
     */
    public function testSearchAnUserWithWrongUsername()
    {
        $users = $this->userService->findBy(['username' => 'fake-user-name']);
        $this->assertTrue(is_array($users));
        $this->assertEmpty($users);
    }

    /**
     * Search for an user that his username does not exists and return a null value
     * @covers \App\Services\Entity\UserService::findOneBy
     * @group user management
     */
    public function testSearchAnUserWithWrongUsernameRetrieveNull()
    {
        $user = $this->userService->findOneBy(['username' => 'fake-user-name']);
        $this->assertNull($user);
    }

    /**
     * Search for an user that his username exists and retrieve a user entity
     * @covers \App\Services\Entity\UserService::findOneBy
     * @group user management
     */
    public function testSearchAnUserWithExistingUsernameRetrieveEntity()
    {
        $user = $this->userService->findOneBy(['username' => 'cory']);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
    }

    public static function tearDownAfterClass()
    {
        UserServiceTest::dropDatabase();
    }
}
