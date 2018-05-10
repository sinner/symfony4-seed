<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use FOS\RestBundle\View\ViewHandlerInterface;
use App\Services\Globals\ApiResponse;
use FOS\RestBundle\View\View;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class LoginException
 * @package App\EventSubscriber
 */
class LoginExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var FirewallMap
     */
    protected $firewallMap;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * LoginExceptionSubscriber constructor.
     * @param ViewHandlerInterface $viewHandler
     * @param HttpUtils $httpUtils
     * @param FirewallMap $firewallMap
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        HttpUtils $httpUtils,
        FirewallMap $firewallMap,
        TranslatorInterface $translator
    ) {
        $this->viewHandler = $viewHandler;
        $this->httpUtils = $httpUtils;
        $this->firewallMap = $firewallMap;
        $this->translator = $translator;
    }

    /**
     * Handles login bad request exceptions by wrapping it with an ApiResponse object.
     * This exception is thrown when a username is missing in the request.
     */
    public function onLoginBadRequestException(GetResponseForExceptionEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if (!($this->viewHandler instanceof ViewHandlerInterface)
            || !($event->getException() instanceof BadRequestHttpException)
            || $this->firewallMap->getFirewallConfig($request)->getName() != 'api_login'
            || $response instanceof ApiResponse
            || !$this->httpUtils->checkRequestPath($request, '/login')
        ) {
            return;
        }

        $response = $this->viewHandler->handle(
            View::create(
                (new ApiResponse)
                ->setMessage($this->translator->trans('login.missing_username'))
                ->setIsSuccess(false)
            )
        );

        $response->headers->set('X-Status-Code', Response::HTTP_UNAUTHORIZED);
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Symfony subscirber priorities range from -255 to 255.
            KernelEvents::EXCEPTION => array('onLoginBadRequestException', 550),
        );
    }
}
