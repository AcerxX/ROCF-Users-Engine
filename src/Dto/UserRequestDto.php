<?php

namespace App\Dto;


class UserRequestDto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var string
     */
    private $locale;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserRequestDto
     */
    public function setId(int $id): UserRequestDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return UserRequestDto
     */
    public function setEmail(string $email): UserRequestDto
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return UserRequestDto
     */
    public function setPassword(string $password): UserRequestDto
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return UserRequestDto
     */
    public function setFirstName(string $firstName): UserRequestDto
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return UserRequestDto
     */
    public function setLastName(string $lastName): UserRequestDto
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return UserRequestDto
     */
    public function setIpAddress(string $ipAddress): UserRequestDto
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return UserRequestDto
     */
    public function setLocale(string $locale): UserRequestDto
    {
        $this->locale = $locale;
        return $this;
    }
}