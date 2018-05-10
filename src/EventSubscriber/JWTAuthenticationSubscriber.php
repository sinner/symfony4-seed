<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\Globals\ApiResponse,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Lexik\Bundle\JWTAuthenticationBundle\Events,
    Lexik\Bundle\JWTAuthenticationBundle\Event,
    JMS\Serializer\SerializerInterface,
    JMS\Serializer\SerializationContext,
    Symfony\Component\Translation\TranslatorInterface;

/**
 * Class JWTAuthentication
 * @package App\EventSubscriber
 */
class JWTAuthenticationSubscriber implements EventSubscriberInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Initialize subscriber.
     *
     * @param SerializerInterface $serializer
     * @param TranslatorInterface $translator
     */
    public function __construct(SerializerInterface $serializer, TranslatorInterface $translator)
    {
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::AUTHENTICATION_FAILURE => ['authenticationFailure', 100],
            Events::AUTHENTICATION_SUCCESS => ['authenticationSuccess', 100],
            Events::JWT_INVALID => ['tokenInvalid', 100]
        ];
    }
    
    /**
     * Handle invalid tokens.
     * 
     * @param Event\JWTInvalidEvent $event
     */
    public function tokenInvalid(Event\JWTInvalidEvent $event)
    {
        $response = $event->getResponse();
        
        $response->setJson(
            $this->serializer
                ->serialize(
                    (new ApiResponse)
                        ->setMessage($this->translator->trans('request.invalid_token'))
                        ->setIsSuccess(false),
                    'json',
                    (new SerializationContext())->setGroups(['api_response'])
                )
        );
        
        $event->setResponse($response);
    }

    /**
     * Handle authentication failures.
     * 
     * @param Event\AuthenticationFailureEvent $event
     */
    public function authenticationFailure(Event\AuthenticationFailureEvent $event)
    {
        $response = $event->getResponse();
        
        $response->setJson(
            $this->serializer
                ->serialize(
                    (new ApiResponse)
                        ->setMessage($response->getMessage())
                        ->setIsSuccess(false),
                    'json',
                    (new SerializationContext())->setGroups(['api_response'])
                )
        );
        
        $event->setResponse($response);
    }

    /**
     * Handle authentication successes.
     * 
     * @param Event\AuthenticationSuccessEvent $event
     */
    public function authenticationSuccess(Event\AuthenticationSuccessEvent $event)
    {
        if (is_array($event->getData()) &&
            array_key_exists('code', $event->getData()) &&
            array_key_exists('data', $event->getData()) &&
            array_key_exists('message', $event->getData()) &&
            array_key_exists('is_success', $event->getData())) {
            return;
        }

        $event->setData(
            $this->serializer
                ->toArray(
                    (new ApiResponse)
                        ->setMessage($this->translator->trans('login.success'))
                        ->setData($event->getData())
                        ->setIsSuccess(true),
                    (new SerializationContext())->setGroups(['api_response'])
                )            
        );
    }
}
