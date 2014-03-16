<?php

namespace Dso\PlannerBundle\Controller;

use Dso\PlannerBundle\Entity\Feedback;
use Dso\PlannerBundle\Form\FeedbackForm;
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
                $em = $this->getDoctrine()->getManager();
                $em->persist($feedbackEntity);
                $em->flush();
                return $this->redirect($this->generateUrl('dso_planner_feedback_sent'));
            }
        }

        return $this->render('DsoPlannerBundle:Feedback:feedback.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function feedbacksentAction()
    {
        return $this->render('DsoPlannerBundle:Feedback:feedback_sent.html.twig');
    }
}
