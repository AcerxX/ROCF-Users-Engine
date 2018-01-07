<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Form\Request\LoginRequest;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param UserService $userService
     * @return \Symfony\Component\Form\FormErrorIterator|JsonResponse
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     */
    public function login(Request $request, UserService $userService)
    {
        $returnData = [
            'isError' => false,
            'errorMessages' => [],
            'userInformation' => []
        ];

        // Get all JSON content from request as Array
        $loginInformation = $request->request->all();
        $userService->setLocaleForTranslator($loginInformation['locale'] ?? null);

        // Check for request errors
        if (\count($errors = $userService->validateLogin($loginInformation))) {
            $returnData['isError'] = true;
            $returnData['errorMessages'] = $errors;
        } else {
            try {
                // If no request errors were found check for the user in database
                /** @var User $user */
                $user = $userService->getUserByEmailAndPassword($loginInformation['email'], $loginInformation['password']);
                // Format User for response
                $returnData['userInformation'] = $userService->formatUserForLoginResponse($user);
            } catch (UserNotFoundException $exception) {
                // If no user is found we return the error.
                // The exception is thrown in Service/UserService.php:125
                $returnData['isError'] = true;
                $returnData['errorMessages'][] = $exception->getMessage();
            }
        }

        return new JsonResponse($returnData);
    }
}