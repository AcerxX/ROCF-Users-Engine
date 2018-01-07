<?php

namespace App\Controller;

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
        $loginInformation = $request->request->all();

        $returnData = [
            'isError' => false
        ];
        if (\count($errors = $userService->validateLogin($loginInformation))) {
            $returnData['isError'] = true;
            $returnData['errorMessages'] = $errors;
        } else {
//            $data = $loginService->getUserInformationOnLogin($loginInformation['email'], $loginInformation['password'], $loginInformation['locale']);
        }

        return new JsonResponse($returnData);
    }
}