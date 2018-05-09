<?php

namespace App\Entity\Abstracts;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\Annotation as JMS;

/**
 * Trait EntityLogTrait
 * @package App\Entity\Abstracts
 */
trait EntityLogTrait
{
    /**
     * A list of reference proxies.
     */
    public $referenceProxies = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @JMS\Accessor(getter="getCreatedAt", setter="SetCreatedAt")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @JMS\Accessor(getter="getUpdatedAt", setter="setUpdatedAt")
     */
    protected $updatedAt;

    /**
     * @return mixed
     */
    public function getReferenceProxies()
    {
        return $this->referenceProxies;
    }

    /**
     * @param mixed $referenceProxies
     * @return EntityInterface
     */
    public function setReferenceProxies($referenceProxies): EntityInterface
    {
        $this->referenceProxies = $referenceProxies;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return EntityInterface
     */
    public function setCreatedAt(\DateTime $createdAt): EntityInterface
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return EntityInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt): EntityInterface
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeCreate()
    {
        $this->publicId = (empty($this->publicId))?sha1(uniqid(get_class($this), true)):$this->publicId;
        $creationDate = new \DateTime();
        $this->setCreatedAt($creationDate);
        $this->setUpdatedAt($creationDate);
    }

    /**
     * @ORM\PreUpdate()
     */
    public function beforeUpdate()
    {
        $this->setCreatedAt(new \DateTime());
    }

}
