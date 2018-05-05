<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Services\Entity\UserService;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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
     * @Operation(
     *     tags={"Authenticated"},
     *     summary="This endpoint gives you the properties of the profile of the user already logged in",
     *     @SWG\Response(
     *         response="200",
     *         description="Request processed.",
     *         @SWG\Schema(ref=@Model(type="App\Entity\User"))
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized request."
     *     )
     * )
     *
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
     * @Operation(
     *     tags={"Authenticated"},
     *     summary="This endpoint gives to users, already logged in, the ability to update its own profile",
     *     @SWG\Parameter(
     *         name="display_name",
     *         in="body",
     *         description="Display Name",
     *         required=false,
     *         type="string",
     *         schema=""
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Request processed.",
     *         @SWG\Schema(ref=@Model(type="App\Entity\User"))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad Request. Some of the data could have an error."
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized request."
     *     )
     * )
     *
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
