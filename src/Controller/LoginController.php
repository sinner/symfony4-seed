<?php

namespace App\Controller;

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
     * @ApiDoc(
     *     section = "Public",
     *     description="This endpoint makes the authentication possible",
     *     method="POST",
     *     parameters={
     *         {"name"="username", "dataType"="string", "required"=true, "description"="User Name"},
     *         {"name"="password", "dataType"="string", "required"=true, "description"="User Password"}
     *     },
     *     statusCodes={
     *         200="Returned when the login process is successful",
     *         401="Returned when the user has not provided his credentials correctly"
     *     }
     * )
     */
    public function postAction()
    {
        // route handled by Lexik JWT Authentication Bundle
        throw new \DomainException('Has occurred an unknown error during the login process');
    }

}
