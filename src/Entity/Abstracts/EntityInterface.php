<?php

declare(strict_types=1);

namespace App\Entity\Abstracts;

use Doctrine\ORM\Mapping as ORM;

/**
 * Interface definition for all entities. Currently used primarily for type hinting.
 *
 * Interface EntityInterface
 * @package App\Entity\Abstracts
 */
interface EntityInterface
{

    public function getId(): int;
    public function getPublicId(): ?string;

}
