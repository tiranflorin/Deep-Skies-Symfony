<?php

namespace Dso\HomeBundle\EventListener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class DsoExceptionListener
 *
 * @package Dso\HomeBundle\EventListener
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DsoExceptionListener
{
    /** @var  \Swift_Mailer $mailer*/
    protected $mailer;

    /** @var  TwigEngine */
    protected $templateService;

    /** @var  string  $adminEmail*/
    protected $adminEmail;

    /**
     * Hook into the exception event and send the administrator
     * a notification email with the exception details.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $message = $this->mailer->createMessage()
            ->setSubject('[Deep-Skies.com] Exception encountered')
            ->setFrom('host_email@deep-skies.com')
            ->setTo($this->adminEmail)
            ->setBody(
                $this->templateService->render(
                    'DsoHomeBundle:Home:email_exception.html.twig',
                    array(
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage(),
                        'stackTrace' => $exception->getTrace()
                    )
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);
    }

    /**
     * @param string $adminEmail
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * @param \Symfony\Bundle\TwigBundle\TwigEngine $templateService
     */
    public function setTemplateService($templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @param \Swift_Mailer $mailer
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }
}
