<?php

namespace App\Controller;

use App\Dto\UserEditRequestDto;
use App\Dto\UserRequestDto;
use App\Exception\UserNotFoundException;
use App\Service\EmailService;
use App\Service\UserService;
use App\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function login(Request $request, UserService $userService): JsonResponse
    {
        $email = $request->request->get('email');

        $returnData = [
            'isError' => false
        ];

        try {
            $user = $userService->getUserFromDatabase($email);
            $returnData['userInformation'] = $userService->formatUserForResponse($user, true);
        } catch (UserNotFoundException $e) {
            $returnData['isError'] = true;
            $returnData['errorMessage'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function register(Request $request, UserService $userService): JsonResponse
    {
        // Get all JSON content from request and denormalize it as UserRequestDto
        $serializer = new Serializer([new ObjectNormalizer()]);
        /** @var UserRequestDto $userRequestDto */
        $userRequestDto = $serializer->denormalize($request->request->all(), UserRequestDto::class);

        // Set the provided locale on the service. It will be used in case of any error
        $userService->setLocaleForTranslator($userRequestDto->getLocale());

        // Create dummy response
        $returnData = [
            'isError' => false
        ];

        try {
            $returnData['userInformation'] = $userService->registerUser($userRequestDto);
        } catch (\Exception $e) {
            $returnData['isError'] = true;
            $returnData['errorMessage'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }

    public function resetPassword(Request $request, UserService $userService, EmailService $emailService)
    {
        $email = $request->request->get('email');
        $locale = $request->request->get('locale');

        // Set the provided locale on the service. It will be used in case of any error
        $userService->setLocaleForTranslator($locale);

        // Create dummy response
        $returnData = [
            'isError' => false
        ];

        try {
            $token = $userService->getTokenForEmail($email);

            $emailBody = $this->renderView(
                'Accounts/resetPassword.html.twig',
                [
                    'resetPasswordLink' =>
                        'http://'
                        . $this->container->getParameter('web_experience_host')
                        . '/reset-password?token='
                        . $token
                ]
            );

            $emailService->sendEmail($email, 'Resetarea parolei pe platforma RoProjects', $emailBody);
        } catch (\Exception $e) {
            $returnData['isError'] = true;
            $returnData['errorMessage'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }


    public function editProfile(Request $request, UserService $userService): JsonResponse
    {
        // Get all JSON content from request and denormalize it as UserRequestDto
        $serializer = new Serializer([new ObjectNormalizer()]);
        /** @var UserEditRequestDto $userEditRequestDto */
        $userEditRequestDto = $serializer->denormalize($request->request->all(), UserEditRequestDto::class);

        // Set the provided locale on the service. It will be used in case of any error
        $userService->setLocaleForTranslator($userEditRequestDto->getLocale());

        // Create dummy response
        $returnData = [
            'isError' => false
        ];

        try {
            $returnData['userInformation'] = $userService->editUserProfile($userEditRequestDto);
        } catch (\Exception $e) {
            $returnData['isError'] = true;
            $returnData['errorMessage'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }
}
