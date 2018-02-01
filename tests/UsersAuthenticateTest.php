<?php

namespace App\Tests;

class UsersAuthenticateTest extends BaseApiTest
{
    public function testEditInfoPass()
    {
        $keysToTest = [
            'isError' => true,
            'userInformation' => [
                'userId' => true,
                'firstName' => true,
                'lastName' => true,
                'roles' => false
            ]
        ];

        $parameters = [
            'email' => 'al3x1393@gmail.com',
            'changes' => [
                'password' => 'pisici12'
            ]
        ];
        $response = $this->getApiResponse('/edit-profile', self::METHOD_POST, $parameters);

        $this->checkResponse($response, $keysToTest);
    }

    public function testEditInfoFail()
    {
        $keysToTest = [
            'isError' => true,
            'errorMessage' => true
        ];

        $parameters = [
            'email' => 'al3x1393@gmail.com',
            'changes' => [
                'password' => ''
            ]
        ];
        $response = $this->getApiResponse('/edit-profile', self::METHOD_POST, $parameters);

        $this->checkResponse($response, $keysToTest, true);
    }
}
