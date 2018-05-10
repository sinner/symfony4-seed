<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\HttpKernel\KernelEvents,
    FOS\RestBundle\View\ViewHandlerInterface,
    App\Services\Globals\ApiResponse,
    Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Context\Context,
    Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InvalidTokenException
 * @package App\EventSubscriber
 */
class InvalidTokenExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ViewHandlerInterface $viewHandler
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        TranslatorInterface $translator
    ) {
        $this->viewHandler = $viewHandler;
        $this->translator = $translator;
    }

    /**
     * Handles authentication credentials not found exception.
     *
     * This exception is thrown when performing a request with an invalid or missing token.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onAuthenticationCredentialsNotFoundException(GetResponseForExceptionEvent $event)
    {
        $response = $event->getResponse();

        if (!($this->viewHandler instanceof ViewHandlerInterface)
            || !($event->getException() instanceof AuthenticationCredentialsNotFoundException)
            || $response instanceof ApiResponse
        ) {
            return;
        }

        $response = $this->viewHandler->handle(
            View::create(
                (new ApiResponse)
                    ->setMessage($this->translator->trans('request.invalid_token'))
                    ->setIsSuccess(false)
            )->setContext(
                (new Context)->setGroups(['api_response'])
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
            KernelEvents::EXCEPTION => array('onAuthenticationCredentialsNotFoundException', 550),
        );
    }
}
