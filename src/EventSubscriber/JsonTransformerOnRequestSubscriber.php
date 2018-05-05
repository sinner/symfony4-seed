<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonTransformerOnRequest
 * @package App\EventSubscriber
 */
class JsonTransformerOnRequestSubscriber implements EventSubscriberInterface
{
    /**
     * This method handles every request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $content = $request->getContent();
        if (empty($content)) {
            return;
        }
        if (!$this->isJsonRequest($request)) {
            return;
        }
        if (!$this->transformJsonBody($request)) {
            throw new BadRequestHttpException('The data sent have as Content-Type header value \'json\' but it doesn\'t have the JSON format.');
        }
    }

    /**
     * This method indicates whether a request content type has a JSON format or not.
     *
     * @param Request $request
     * @return bool
     */
    private function isJsonRequest(Request $request): bool
    {
        return 'json' === $request->getContentType();
    }

    /**
     * It transforms the json content that is into a request
     * It returns true if this action is possible, otherwise, return false
     *
     * @param Request $request
     * @return bool
     */
    private function transformJsonBody(Request $request): bool
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        if ($data === null) {
            return true;
        }
        $request->request->replace($data);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 100),
        );
    }
}