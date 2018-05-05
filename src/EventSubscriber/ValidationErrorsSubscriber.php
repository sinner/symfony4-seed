<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\ViewHandlerInterface;
use App\Services\Globals\ApiResponse;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ValidationErrors
 * @package App\EventSubscriber
 */
class ValidationErrorsSubscriber implements EventSubscriberInterface
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // Must be executed after FOSREST's Body listener
            KernelEvents::CONTROLLER => ['handleValidationErrors', -100]
        ];
    }

    /**
     * If validation errors are present from FOSREST's body listener return a reponse.
     *
     * @param FilterControllerEvent $event
     */
    public function handleValidationErrors(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('validationErrors')) return;

        $validationErrors = $request->attributes->get('validationErrors');
        if (!($validationErrors instanceof ConstraintViolationList)) return;

        $errors = [];
        foreach($validationErrors as $validationError) {

            // Camel case to snake case.
            $propertyName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $validationError->getPropertyPath()));

            $errors[$propertyName] = $validationError->getMessage();
        }   

        if (empty($errors)) return;

        $response = $this->viewHandler->handle(
            View::create(
                (new ApiResponse)
                ->setMessage('Validation Errors.')
                ->setData(['errors' => $errors])
                ->setIsSuccess(false)
            )
        );
        
        // Change status code to 400 Bad Request. 
        $response->headers->set('X-Status-Code', Response::HTTP_BAD_REQUEST);
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        $event->stopPropagation();

        $event->setController(
            function() use ($response) {
                return $response;
            }
        );
    }
}
