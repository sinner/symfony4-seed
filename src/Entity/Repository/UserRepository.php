<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Abstracts\EntityRepositoryInterface;

/**
 * Class UserRepository
 * @package App\Entity\Repository
 */
class UserRepository extends EntityRepository implements EntityRepositoryInterface
{

}
