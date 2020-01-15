<?php

namespace App\Controller;

use App\Entity\UserPreferences;
use App\Entity\User;
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
        //$user = $entityManager->getRepository(User::class)->findOneBy([app.user.name]);
        $user =$this->get('security.context')->getToken()->getUser();
        if($user){
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);//$this->getUser()->getId()]);
        }


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
