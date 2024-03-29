<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\HangmanBundle\Entity\Player;
use Sensio\Bundle\HangmanBundle\Form\PlayerType;
use Symfony\Component\Security\Core\SecurityContext;

class PlayerController extends Controller
{

    /**
     * @Route("/signup", name="signup")
     * @Template()
     */
    public function registrationAction(Request $request)
    {
        $player = new Player();
        $form = $this->createForm(new PlayerType(), $player);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($player);
            $player->encodePassword($encoder);

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();

            return $this->redirect($this->generateUrl('hangman_game'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Template()
     */
    public function signinAction(Request $request)
    {
        $session = $request->getSession();
        $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        $session->remove(SecurityContext::AUTHENTICATION_ERROR);

        return array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
        );
    }

    /**
     * @Cache(smaxage = 120)
     * @Template()
     */
    public function playersAction($max)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SensioHangmanBundle:Player');

        return array(
            'players' => $repository->getMostRecentPlayers($max),
        );
    }

}
