<?php

namespace App\Services\Globals;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RestMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $fromEmail;

    /**
     * RestMailer constructor.
     * @param \Swift_Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment $twig
     * @param string $fromEmail
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, string $fromEmail)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->fromEmail = $fromEmail;
    }

    /**
     * @param UserInterface $user
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $template = ':mails/user:register.confirmation.html.twig';

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );
        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());

    }

    /**
     * @param UserInterface $user
     * @return int|null
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendResettingEmailMessage(UserInterface $user): ?int
    {
        $template = ':mails/user:register.confirmation.html.twig';

        $url = $this->router->generate(
            'confirm_password_reset',
            ['token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $context = [
            'user' => $user,
            'confirmationUrl' => $url
        ];

        return $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());

    }

    /**
     * @param $templateName
     * @param $context
     * @param $fromEmail
     * @param $toEmail
     * @return int
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail): ?int
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        $failedRecipients = [];
        $sendMessageResult = $this->mailer->send($message, $failedRecipients);
        return $sendMessageResult;
    }

}