<?php

declare(strict_types=1);

namespace EN\Api\AppBundle\Controller;

use EN\Api\ApiBundle\Controller\AbstractController;
use EN\Api\ApiBundle\Response\Api;
use EN\OneReachBasic\DoctrineBundle\Entity\Service\UserService;
use EN\OneReachBasic\DoctrineBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @RouteResource("profile", pluralize=false)
 */
class ProfileController extends AbstractController implements ClassResourceInterface
{

    /**
     *
     * This endpoint gives you the properties of the profile of the user already logged in
     *
     * @Rest\Get("/profile")
     * @Rest\View(serializerGroups={"public_read", "api_response"})
     *
     * @ApiDoc(
     *     section = "Authenticated",
     *     authentication = true,
     *     description = "This endpoint gives you the properties of the profile of the user already logged in",
     *     method = "GET",
     *     headers = {
     *         {"name":"Authorization", "required":true, "description":"Your login token."}
     *     },
     *     statusCodes = {
     *         200="Request processed.",
     *         401="Unauthorized request."
     *     },
     *     output = {
     *         "class"="EN\OneReachBasic\DoctrineBundle\Entity\User",
     *         "groups"={"public_read"}
     *     }
     * )
     *
     * @return User
     *
     */
    public function getAction(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }

    /**
     * This endpoint gives to users, already logged in, the ability to update its own profile
     *
     * @Rest\Put("/profile")
     * @Rest\View(serializerGroups={"public_read", "api_response"})
     *
     * @ParamConverter("userToUpdate", converter="fos_rest.request_body",
     *     options = {"validate" = false, "deserializationContext" = {"groups"={"auth_write"}}}
     * )
     *
     * @ApiDoc(
     *     section = "Authenticated",
     *     authentication = true,
     *     description = "This endpoint gives to users, already logged in, the ability to update its own profile",
     *     method = "PUT",
     *     headers = {
     *         {"name":"Authorization", "required":true, "description":"Your login token."}
     *     },
     *     parameters = {
     *         {"name"="display_name", "dataType"="string", "required"=true, "description"="Display Name"}
     *     },
     *     statusCodes = {
     *         200="Request processed.",
     *         400="Bad Request. Some of the data could have an error.",
     *         401="Unauthorized request."
     *     },
     *     output = {
     *         "class"="EN\OneReachBasic\DoctrineBundle\Entity\User",
     *         "groups"={"public_read"}
     *     }
     * )
     *
     * @throws \EN\Api\ApiBundle\Exception\Api
     *
     * @param Request $request
     * @param User $userToUpdate
     *
     * @return User
     *
     */
    public function putAction(Request $request, User $userToUpdate): User
    {

        $user = $this->getUser();

        /** @var UserService $userService */
        $userService = $this->getEntityService('User');

        $request->attributes->set(
            'api_response_message',
            $this->get('translator')->trans('profile.message.success')
        );

        return $userService->updateUser($user, $userToUpdate);

    }

}
