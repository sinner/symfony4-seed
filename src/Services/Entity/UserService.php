<?php

namespace App\Services\Entity;

use App\Services\Entity\Abstracts\EntityServiceAbstract;
use Doctrine\ORM\EntityManager;
use App\Entity\Repository\UserRepository;

/**
 * Class UserService
 * @package App\Services\Entity
 */
class UserService extends EntityServiceAbstract
{
    /**
     * @param EntityManager $entityManager
     * @param UserRepository $entityRepository
     */
    public function __construct(EntityManager $entityManager, UserRepository $entityRepository)
    {
        parent::__construct($entityManager, $entityRepository);
    }

}
