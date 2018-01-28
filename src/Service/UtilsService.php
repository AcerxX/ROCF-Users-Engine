<?php

namespace App\Service;

class UtilsService
{
    /**
     * Generates a random token that can be used in anytime, anywhere
     *
     * @param string $optionalString
     * @return string
     */
    public static function generateRandomToken(string $optionalString = ''): string
    {
        try {
            $token = md5(uniqid(random_bytes(5) . $optionalString, true));
        } catch (\Exception $e) {
            $token = md5(uniqid($optionalString, true));
        }

        return $token;
    }
}
