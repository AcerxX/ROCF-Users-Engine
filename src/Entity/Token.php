<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TokenRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Token
{
    public const TYPE_RESET_PASSWORD = 1;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $token;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tokens")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $expireDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $modified;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Token
     */
    public function setToken(string $token): Token
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Token
     */
    public function setUser(User $user): Token
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Token
     */
    public function setType(int $type): Token
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Token
     */
    public function setStatus(int $status): Token
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpireDate(): \DateTime
    {
        return $this->expireDate;
    }

    /**
     * @param \DateTime $expireDate
     * @return Token
     */
    public function setExpireDate(\DateTime $expireDate): Token
    {
        $this->expireDate = $expireDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return Token
     */
    public function setCreated(\DateTime $created): Token
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified(): \DateTime
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $modified
     * @return Token
     */
    public function setModified(\DateTime $modified): Token
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        if ($this->created === null) {
            $this->created = new \DateTime();
        }
        $this->modified = new \DateTime();

        if ($this->expireDate === null) {
            $this->expireDate = (new \DateTime())->modify('+15 minutes');
        }
    }
}
