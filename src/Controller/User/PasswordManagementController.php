<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Services\Globals\ApiResponse;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @RouteResource("password", pluralize=false)
 */
class PasswordManagementController extends AbstractController implements ClassResourceInterface
{
    /**
     * This endpoint gives to users, already registered, the ability to request the reset of its own password
     * It's required to provide the email or the username to this operation
     *
     * @Rest\Post("/public/password/reset/request")
     * @Rest\View(serializerGroups={"public_read", "api_response"})
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="This endpoint gives to users, already registered, the ability to request the reset of its own password It's required to provide the email or the username to this operation",
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Username or Email",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Request processed."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad Request. Some of the data could have an error."
     *     )
     * )
     *
     *
     * @param Request $request
     * @return ApiResponse
     * @throws ApiException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function requestResetAction(Request $request): ?ApiResponse
    {
        $username = $request->request->get('username');
        $exception = new ApiException();
        $apiResponse = new ApiResponse();

        /** @var $user User */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null === $user) {
            $exception->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
            $exception->setMessage($this->get('translator')->trans('user.username.not_found'));
            $apiResponse->setIsSuccess(false);
            $apiResponse->setMessage($this->get('translator')->trans('user.username.not_found'));
            throw $exception;

        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

        if (null !== $event->getResponse()) {
            $apiResponse->setData($event->getResponse());
            return $apiResponse;
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {

            $exception->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
            $exception->setMessage($this->get('translator')->trans('password_request.resetting.password_already_requested'));
            throw $exception;

        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        /* Dispatch confirm event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

        if (null !== $event->getResponse()) {
            $apiResponse->setData($event->getResponse());
            return $apiResponse;
        }

        $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
        // $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);


        /* Dispatch completed event */
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);



        if (null !== $event->getResponse()) {
            $apiResponse->setData($event->getResponse());
            return $apiResponse;
        }

        $apiResponse->setMessage($this->get('translator')->trans(
            'resetting.check_email',
            ['%tokenLifetime%' => floor($this->container->getParameter('fos_user.resetting.token_ttl') / 3600) ],
            'FOSUserBundle'));

        $apiResponse->setIsSuccess(true);

        return $apiResponse;

    }

    /**
     * This endpoint gives to users, already registered, the ability to reset its own password.
     * It's required to provide the current password, new password and password confirmation
     *
     * @Rest\Post("/public/password/reset/confirm")
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="This endpoint gives to users, already registered, the ability to reset its own password. It's required to provide the current password, new password and password confirmation",
     *     @SWG\Parameter(
     *         name="token",
     *         in="formData",
     *         description="todo",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Request processed."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad Request. Some of the data could have an error."
     *     )
     * )
     *
     *
     * @param Request $request
     * @return Response
     *
     * @throws ApiException
     */
    public function confirmResetAction(Request $request): Response
    {
        $token = $request->request->get('token', null);
        $exception = new ApiException();
        if (null === $token) {
            $exception->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
            $exception->setData($this->get('translator')->trans('password_request.resetting.token_missing'));
            throw $exception;
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            $exception->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
            $exception->setData($this->get('translator')->trans('password_request.resetting.token_do_not_match'));
            throw $exception;
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var Form $form */
        $form = $formFactory->createForm([
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
        $form->setData($user);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            $exception = new ApiException();
            $exception->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
            $exception->setMessage($this->get('translator')->trans('password_request.resetting.form_errors'));
            throw $exception;
        }

        $event = new FormEvent($form, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            return new JsonResponse(
                $this->get('translator')->trans('resetting.flash.success', [], 'FOSUserBundle'),
                JsonResponse::HTTP_OK
            );
        }

        // unsure if this is now needed / will work the same
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

        return new JsonResponse(
            $this->get('translator')->trans('resetting.flash.success', [], 'FOSUserBundle'),
            JsonResponse::HTTP_OK
        );
    }


    /**
     * This method Change user password
     *
     * @Rest\Put("/password/")
     * @Rest\View(serializerGroups={"api_response"})
     *
     * @Operation(
     *     tags={"Authenticated"},
     *     summary="This method Change user password",
     *     @SWG\Parameter(
     *         name="token",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         type="string",
     *         schema=""
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Request processed."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad Request. Some of the data could have an error."
     *     )
     * )
     *
     *
     *
     * @param Request $request
     * @return ApiResponse
     * @throws ApiException
     *
     */
    public function changeAction(Request $request): ApiResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(ChangePasswordFormType::class, $user, [
            'csrf_protection'    => false
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            $exception = new ApiException();
            $exception->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
            $exception->setData($form);
            throw $exception;
        }

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $event = new FormEvent($form, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

        $userManager->updateUser($user);

        $apiResponse = new ApiResponse();
        if (null === $response = $event->getResponse()) {
            $apiResponse->setMessage($this->get('translator')->trans('change_password.flash.success', [], 'FOSUserBundle'));
            $apiResponse->setData(['message' => $this->get('translator')->trans('change_password.flash.success', [], 'FOSUserBundle')]);
            $apiResponse->setIsSuccess(true);
        }

        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
            new FilterUserResponseEvent($user, $request, new JsonResponse(
                $this->get('translator')->trans('change_password.flash.success', [], 'FOSUserBundle'),
                JsonResponse::HTTP_OK
            )));

        $apiResponse->setMessage($this->get('translator')->trans('change_password.flash.success', [], 'FOSUserBundle'));
        $apiResponse->setData(['message' => $this->get('translator')->trans('change_password.flash.success', [], 'FOSUserBundle')]);
        $apiResponse->setIsSuccess(true);

        return $apiResponse;
    }

}