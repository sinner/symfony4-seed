<?php

namespace App\Entity;

use App\Entity\Abstracts\EntityInterface;
use App\Entity\Abstracts\EntityLogTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(
 *     name="users",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="email_uidx", columns={"email"}),
 *        @ORM\UniqueConstraint(name="username_uidx", columns={"username"}),
 *        @ORM\UniqueConstraint(name="public_uidx", columns={"public_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @UniqueEntity("publicId")
 *
 */
class User extends BaseUser implements EntityInterface
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Groups({"admin_read", "admin_write"})
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getId")
     */
    protected $id;

    /**
     * @ORM\Column(name="public_id", type="string")
     * @JMS\Groups({"admin_read", "public_read", "admin_write"})
     * @JMS\Type("string")
     */
    protected $publicId;

    /**
     * @Assert\NotBlank(message="user.username.not_blank")
     *
     * @Assert\Length(max="20", min="3", maxMessage="user.username.length.max", minMessage="user.username.length.min")
     *
     * @JMS\Groups({"admin_read", "public_read", "admin_write"})
     * @JMS\Type("string")
     */
    protected $username;

    /**
     * @ORM\Column(name="display_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="user.display_name.not_blank")
     *
     * @JMS\Groups({"admin_read", "public_read", "admin_write"})
     * @JMS\Type("string")
     */
    protected $displayName;

    /**
     * @var string
     *
     * @JMS\Groups({"admin_read", "public_read", "admin_write"})
     * @JMS\Type("string")
     *
     * @Assert\Email(message="user.email.email")
     * @Assert\NotBlank(message="user.email.not_blank")
     */
    protected $email;

    use EntityLogTrait;

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param $displayName
     * @return User
     */
    public function setDisplayName(string $displayName): User
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublicId(): string
    {
        return $this->publicId;
    }

    /**
     * @param mixed $publicId
     * @return User
     */
    public function setPublicId($publicId)
    {
        $this->publicId = $publicId;
        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeCreate() {
        $this->publicId = sha1($this->username.$this->email.date('Y-m-d H:i:s'));
        $this->confirmationToken = (empty($this->confirmationToken))?sha1($this->username.$this->email.date('Y-m-d H:i:s')):$this->confirmationToken;
        $this->displayName = (empty($this->displayName))?$this->getUsername():$this->displayName;
        if(count($this->roles)===0) {
            $this->addRole('ROLE_USER');
        }
    }

}