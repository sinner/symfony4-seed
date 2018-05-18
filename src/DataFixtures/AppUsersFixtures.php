<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Faker\Factory;

/**
 * Class AppUsersFixtures
 * @package App\DataFixtures
 */
class AppUsersFixtures extends Fixture implements FixtureInterface
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * AppUsersFixtures constructor.
     * @param UserManagerInterface $userManager
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     */
    public function __construct(UserManagerInterface $userManager, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->userManager = $userManager;
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var array $users */
        $users = [
            [
                'username'    => 'cory',
                'displayName' => 'Cory Taylor',
                'password' => 'corypass',
                'email' => 'cory@teravisiontech.com',
                'confirmationToken' => '',
                'role' => 'ROLE_USER',
            ],
            [
                'username'    => 'eddy',
                'displayName' => 'Edward Smith',
                'password' => 'eddypass',
                'email' => 'eddy@teravisiontech.com',
                'confirmationToken' => 'some-token-string',
                'role' => 'ROLE_USER',
            ],
            [
                'username'    => 'joshua',
                'displayName' => 'Joshua James',
                'password' => 'joshuapass',
                'email' => 'joshua@teravisiontech.com',
                'confirmationToken' => '',
                'role' => 'ROLE_ADMIN',
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setPlainPassword($userData['password']);
            $user->setEmail($userData['email']);
            $user->setDisplayName($userData['displayName']);
            $user->setEnabled(true);
            $user->addRole($userData['role']);
            if(strlen($userData['confirmationToken'])>0){
                $user->setPasswordRequestedAt(new \DateTime('now'));
            }
            $this->userManager->updateUser($user);
        }
        //create a 50 users with faker
        $faker = Factory::create();
        for ($i = 0; $i < 50; $i++) {
            $fakeUser = new User();
            $fakeUser->setUsername($faker->unique()->userName);
            $fakeUser->setPlainPassword($faker->word);
            $fakeUser->setEmail($faker->unique()->email);
            $fakeUser->setDisplayName($faker->name);
            $fakeUser->setEnabled(true);
            $fakeUser->addRole('ROLE_ADMIN');
            $this->userManager->updateUser($fakeUser);
        }
    }
}