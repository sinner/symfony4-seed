<?php

declare(strict_types=1);

namespace App\Services\Entity;

use App\Entity\Abstracts\EntityRepositoryInterface;
use App\Services\Entity\Abstracts\EntityServiceAbstract;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserService
 * @package App\Services\Entity
 */
class UserService extends EntityServiceAbstract
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param EntityRepositoryInterface $entityRepository
     */
    public function __construct(EntityManagerInterface $entityManager, EntityRepositoryInterface $userRepository)
    {
        parent::__construct($entityManager, $userRepository);
    }

}
