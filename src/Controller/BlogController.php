<?php

namespace App\Controller;

use App\Entity\Relation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * Populate empty table Relation with content in
     * file relations.json.
     */
    public function populateRelationTableFromJson(){
        $entityManager = $this->getDoctrine()->getManager();
        $jsondata = file_get_contents('relations.json');
        $data = json_decode($jsondata, true);

        foreach($data as $item) {
            $entity = new Relation();
            $entity->setIdRelation($item['id']);
            $entity->setName($item['name']);
            $entity->setDescription($item['description']);
            $entity->setWeigth(0);

            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }

    /**
     * @Route("/", name="homepage")
     * TODO relations get 20 best relations
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $relations = $entityManager->getRepository(Relation::class)->findAll();
        // database empty, populate it
        if(!$relations) {
            $this->populateRelationTableFromJson();
        }
        // fetch relations from DB.
        $relations = $entityManager->getRepository(Relation::class)->findAll();
        //$relations = ['toto', 'titi', 'tata', 'tutu', 'tete'];
        return $this->render('blog/index.html.twig', [
            'title' => 'Bienvenue',
            'relations' => $relations]);
    }
}
