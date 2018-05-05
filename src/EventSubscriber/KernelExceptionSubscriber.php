<?php

namespace App\EventSubscriber;

use JMS\Serializer\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use FOS\RestBundle\View\ViewHandlerInterface;
use App\Services\Globals\ApiResponse;
use App\Exception\ApiException;
use FOS\RestBundle\View\View;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class KernelException
 * @package App\EventSubscriber
 */
class KernelExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @param ViewHandlerInterface $viewHandler
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param string $environment
     */
    public function __construct(ViewHandlerInterface $viewHandler, LoggerInterface $logger, TranslatorInterface $translator, string $environment)
    {
        $this->viewHandler = $viewHandler;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->environment = $environment;
    }

    /**
     * Wraps all exceptions with an ApiResponse object.
     *
     * {@inheritdoc}
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $response = $event->getResponse();

        if (!($this->viewHandler instanceof ViewHandlerInterface)
            || $response instanceof ApiResponse
        ) {
            return;
        }

        $response = (new ApiResponse)
            ->setMessage($this->translator->trans('request.unknown_error'))
            ->setData(
                [
                    // Lets only expose exception messages and stacktraces on non-prod environments to minize risk of exposing sensitive information.
                    // If errors occur on prod, we can check the logs.
                    'details' => ($this->environment!='prod') ? (string)$event->getException() : ''
                ]
            )
            ->setIsSuccess(false);

        /** @var BadRequestHttpException $exception */
        $exception = $event->getException();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof ApiException) {
            $statusCode = $exception->getStatusCode();
            $response->setData($exception->getData())
                ->setMessage($exception->getMessage());
        }
        elseif (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }

        $this->logger->error($exception);

        $event->setResponse($this->viewHandler->handle(View::create($response, $statusCode)));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Symfony subscirber priorities range from -255 to 255.
            KernelEvents::EXCEPTION => array('onKernelException', 500),
        );
    }
}
