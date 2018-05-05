<?php

namespace App\Controller;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Services\Globals\ApiResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\RouteResource("login", pluralize=false)
 */
class LoginController extends FOSRestController implements ClassResourceInterface
{

    /**
     * This endpoint lets the user login into the system and it will return a token (JWT)
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="his endpoint makes the authentication possible",
     *     method="POST",
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         description="Username",
     *         required=true,
     *         type="string",
     *         schema=""
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="body",
     *         description="User Password",
     *         required=true,
     *         type="string",
     *         schema=""
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when the login process is successful"
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Returned when the user has not provided his credentials correctly"
     *     )
     * )
     */
    public function postAction()
    {
        // route handled by Lexik JWT Authentication Bundle
        throw new \DomainException('Has occurred an unknown error during the login process');
    }

}
