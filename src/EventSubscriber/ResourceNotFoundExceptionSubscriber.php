<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use FOS\RestBundle\View\ViewHandlerInterface;
use App\Services\Globals\ApiResponse;
use FOS\RestBundle\View\View;
use App\Exception\ResourceNotFoundException;

/**
 * Class ResourceNotFoundException
 * @package App\EventSubscriber
 */
class ResourceNotFoundExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @param ViewHandlerInterface $viewHandler
     */
    public function __construct(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    /**
     * Handles resource not found exception thrown by the api
     */
    public function onResourceNotFoundException(GetResponseForExceptionEvent $event)
    {
        $response = $event->getResponse();
        $exception = $event->getException();

        if (   !($this->viewHandler instanceof ViewHandlerInterface)
            || !($exception instanceof ApiResourceNotFoundException)
            || !($exception instanceof NotFoundHttpException)
            || $response instanceof ApiResponse
        ) {
            return;
        }

        $response = $this->viewHandler->handle(
            View::create(
                (new ApiResponse)
                    ->setMessage($exception->getMessage())
                    ->setIsSuccess(false)
            )
        );

        $response->headers->set('X-Status-Code', Response::HTTP_NOT_FOUND);
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Symfony subscirber priorities range from -255 to 255.
            KernelEvents::EXCEPTION => array('onResourceNotFoundException', 550),
        );
    }
}
