<?php

namespace App\Controller;

use App\Entity\UserPreferences;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deals with connection, and user params
 */
class UserProfilController extends AbstractController
{
    /**
     * @Route("/preferences/{username}", name="preferences")
     */
    public function index($username, Request $request)
    {
        // fetch from DB
        $entityManager = $this->getDoctrine()->getManager();

        // fetch user
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
        if($user){
            // fetch user preferences.
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);
        }

        // set-up a UserPref object to render in form
        $prefs = new UserPreferences();
        $isUpdate = false;
        if($preferences){
            // we have some preferences stored in DB for this user
            $isUpdate = true;
            $prefs->setMaxDisplay($preferences->getMaxDisplay());
            $prefs->setDisplayOrder($preferences->getDisplayOrder());
        } else {
            // we use default preferences
            $prefs->setMaxDisplay(10);
            $prefs->setDisplayOrder("alphabetique");
        }

        // based on preferences, build the form
        $form = $this->createFormBuilder($prefs)
            ->add('max_display', TextType::class,)
            ->add('display_order', ChoiceType::class, ['choices'  => [
                'Alphabétique' => "alpha",
                'Poids décroissant' => "poids", ],
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        // if OK, save prefs in DB
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            $newPrefs = $form->getData();
            // save to database
            if($isUpdate){
                // update entity value
                $preferences->setMaxDisplay($newPrefs->getMaxDisplay());
                $preferences->setDisplayOrder($newPrefs->getDisplayOrder());
                $preferences->setRelationsNotDisplay($newPrefs->getRelationsNotDisplay());
                $preferences->setUserId($user);
            } else {
                // insert new entity in DB
                $newPrefs->setUserId($user);
                $newPrefs->setRelationsNotDisplay(array());
                $entityManager->persist($newPrefs);
            }
            $entityManager->flush();

            // now data is saved, user go back to homepage
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user_profil/index.html.twig', [
            'form' => $form->createView(),
            'is_alpha_selected' => $prefs->isAlphaSelected(),
        ]);
    }
}
