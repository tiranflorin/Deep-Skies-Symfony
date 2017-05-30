<?php

namespace Dso\HomeBundle\Controller;

use Dso\HomeBundle\Entity\Feedback;
use Dso\HomeBundle\Form\Type\FeedbackForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends Controller
{
    public function feedbackAction(Request $request)
    {
        $feedbackEntity = new Feedback();
        $form = $this->createForm(new FeedbackForm(), $feedbackEntity);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $mailer = $this->get('mailer');
                $message = $mailer->createMessage()
                    ->setSubject('[Deep-Skies.com] Feedback')
                    ->setFrom($feedbackEntity->getEmail())
                    ->setTo($this->container->getParameter('administrator_email'))
                    ->setBody(
                        $this->renderView(
                            'DsoHomeBundle:Feedback:email_template.html.twig',
                            array(
                                'name' => $feedbackEntity->getName(),
                                'message' => $feedbackEntity->getMessage()
                            )
                        ),
                        'text/html'
                    )
                ;
                $mailer->send($message);

                $em = $this->getDoctrine()->getManager();
                $em->persist($feedbackEntity);
                $em->flush();

                return $this->redirectToRoute('dso_home_feedback_sent');
            }
        }

        return $this->render('DsoHomeBundle:Feedback:feedback.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function feedbacksentAction()
    {
        return $this->render('DsoHomeBundle:Feedback:feedback_sent.html.twig');
    }
}
