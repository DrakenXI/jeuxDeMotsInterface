<?php

namespace App\Controller;

use App\Entity\UserPreferences;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deals with connection, and user params
 */
class UserProfilController extends AbstractController
{
    /**
     * @Route("/preferences", name="preferences")
     */
    public function index()
    {

        // fetch post from DB
        $entityManager = $this->getDoctrine()->getManager();
        $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy(['user_id' => $this->getUser()->id]);

        if(!$preferences){
            return $this->render('user_profil/index.html.twig', [
                'max_display' => 20,
                'display_order' => "alphabetique",
                'relations_not_display' => [],
            ]);
        }else{
            return $this->render('user_profil/index.html.twig', [
                'max_display' => $preferences->max_display,
                'display_order' => $preferences->display_order,
                'relations_not_display' => $preferences->relations_not_display,
            ]);
        }


    }
}
