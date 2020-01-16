<?php

namespace App\Controller;

use App\Entity\UserPreferences;
use App\Entity\User;
use App\Entity\Relation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deals with connection, and user params
 */
class UserProfilController extends AbstractController
{
    /**
     * @Route("/preferences/{username}", name="preferences")
     */
    public function index($username)
    {

        // fetch post from DB

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
        //$user =$this->get('security.context')->getToken()->getUser();
        if($user){
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);//$this->getUser()->getId()]);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $relations = $entityManager->getRepository(Relation::class)->findAll();
        if(!$relations){
            $relations = "";
        }

        if(!$preferences){
            return $this->render('user_profil/index.html.twig', [
                'relations' => $relations,
                'max_display' => 20,
                'display_order' => "alphabetique",
                'is_alpha_selected' => "selected",
                'is_weight_selected' => "",
                'relations_not_display' => [],
            ]);
        }else{
            return $this->render('user_profil/index.html.twig', [
                'max_display' => $preferences->max_display,
                'display_order' => $preferences->display_order,
                'is_alpha_selected' => $preferences->isAlphaSelected,
                'is_weight_selected' => !$preferences->isAlphaSelected,
                'relations_not_display' => $preferences->relations_not_display,
            ]);
        }
    }

    /**
     * @Route("/preferences/{username}/save", name="save-preferences")
     */
    public function savePref($username)
    {
        return $this->redirectToRoute('homepage');
    }
}
