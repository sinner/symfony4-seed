<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\Globals\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class KernelView
 * @package App\EventSubscriber
 */
class KernelViewSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // Must be executed before FOSREST's View listener
            KernelEvents::VIEW => ['standardizeResponse', 100],
        ];
    }

    /**
     * Standardizes all API responses.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function standardizeResponse(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        $requestAttributes = $event->getRequest()->attributes;

        if ($controllerResult instanceof ApiResponse){
            return;
        }

        $event->setControllerResult(
            (new ApiResponse)
                ->setMessage(
                    $requestAttributes->has('api_response_message') ? 
                        $requestAttributes->get('api_response_message') :
                        $this->translator->trans('default.success')
                )
                ->setData($controllerResult)
        );
    }
}
