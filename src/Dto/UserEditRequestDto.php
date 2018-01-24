<?php

namespace App\Dto;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserEditRequestDto
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var UserChangesDto
     */
    private $changes;

    /**
     * @var string
     */
    private $locale = 'ro';

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return UserEditRequestDto
     */
    public function setEmail(string $email): UserEditRequestDto
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return UserChangesDto
     */
    public function getChanges(): UserChangesDto
    {
        return $this->changes;
    }

    /**
     * @param UserChangesDto $changes
     * @return UserEditRequestDto
     */
    public function setChanges(array $changes): UserEditRequestDto
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        /** @var UserEditRequestDto $userEditRequestDto */
        $userEditRequestDto = $serializer->denormalize($changes, UserChangesDto::class);

        $this->changes = $userEditRequestDto;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return UserEditRequestDto
     */
    public function setLocale(string $locale): UserEditRequestDto
    {
        $this->locale = $locale;
        return $this;
    }
}
