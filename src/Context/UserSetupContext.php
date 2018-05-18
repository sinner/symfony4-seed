<?php

declare(strict_types=1);

namespace App\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Schema\Constraint;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Config\Tests\Util\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserSetupContext
 * @package App\Context
 */
class UserSetupContext implements Context, SnippetAcceptingContext
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
     * UserSetupContext constructor.
     *
     * @param UserManagerInterface   $userManager
     * @param EntityManagerInterface $em
     */
    public function __construct(UserManagerInterface $userManager, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->userManager = $userManager;
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @param TableNode $users
     *
     * @Given there are Users with the following details:
     */
    public function thereAreUsersWithTheFollowingDetails(TableNode $users)
    {
        foreach ($users->getColumnsHash() as $key => $val) {

            $confirmationToken = isset($val['confirmation_token']) && $val['confirmation_token'] != ''
                ? $val['confirmation_token']
                : '';

            $user = $this->userManager->createUser();

            $user->setEnabled(true);
            $user->setUsername($val['username']);
            $user->setEmail($val['email']);
            $user->setPlainPassword($val['password']);
            $user->setConfirmationToken($confirmationToken);

            if (!empty($confirmationToken)) {
                $user->setPasswordRequestedAt(new \DateTime('now'));
            }

            try {
                $this->userManager->updateUser($user);
            }
            catch (\Exception $exception) {
                print_r($exception->getMessage());
            }
        }
    }

}