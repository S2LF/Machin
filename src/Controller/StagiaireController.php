<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Form\StagiaireFormType;
use App\Form\AjoutSessionFormType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/stagiaire")
 * @IsGranted("ROLE_USER")
 */


class StagiaireController extends AbstractController
{
   /**
     * @Route("/", name="stagiaire_index")
     * 
     */
    public function index()
    {
        $stagiaires = $this->getDoctrine()
                        ->getRepository(Stagiaire::class)
                        ->getAll();

        return $this->render('stagiaire/index.html.twig', [
            'stagiaires' => $stagiaires,
        ]);
    }
    
    /**
    * @Route("/new", name="form_stagiaire")
    * @IsGranted("ROLE_ADMIN")
    */
    public function newForm(Request $request){

        //On instancie un nouvel objet Stagiaire
        $newStagiaire = new Stagiaire();
        //On crée le formulaire avec cet objet vide
        $form = $this->createForm(StagiaireFormType::class, $newStagiaire);

        //On récupère les données du formulaire
        $form->handleRequest($request);
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            $newStagiaire = $form->getData();

            //Grâce à Doctrine, on instancie l'entité, on crée la donnnée (persist) 
            //et on met à jours la BDD (flush)
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newStagiaire);
            $entityManager->flush();
            //On redirige vers la liste des stagiaires avec un message de validation
            $this->addFlash("success", "Le stagiaire a bien été ajouté à la liste !");
            return $this->redirectToRoute('stagiaire_index');
        }

        //On envoie le formulaire vers la vue
        return $this->render('stagiaire/stagiaireForm.html.twig', [
        'stagiaire_form' => $form->createView()
        ]);
     }

     
    /**
    * @Route("/newSession/{id}", name="ajout_session")
    */
    public function AjoutSessionForm(Stagiaire $stagiaire, Request $request, EntityManagerInterface $em){


          // Liste déroulante adapté
          $sessions = $em->getRepository(Session::class)->findAll();

          foreach($sessions as $key => $session){
              if($stagiaire->getSessions()->contains($session) || ($session->getNbPlaces() - count($session->getStagiaires()) <= 0)){
                  unset($sessions[$key]);
              }
          }
  
          if($session_id = $request->request->get('session')){
              $session = $em->getRepository(Session::class)->findOneBy(['id' => $session_id]);
  
              $stagiaire->addSession($session);
             
              $em->flush();
              $this->addFlash("success", "Session bien ajouté !");
              return $this->redirectToRoute("showOne_stagiaire", array('id' => $stagiaire->getId()));
          }
          
          return $this->render('stagiaire/ajoutSessionForm.html.twig', [
              'sessionsDispo' => $sessions,
              //"ajout_stagiaire_form" => $form->createView(),
              'stagiaire' => $stagiaire
          ]);




        // $form = $this->createForm(AjoutSessionFormType::class);

        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
            
        //     $newAjoutSession = $form->get('session')->getData();

        //     $stagiaire->addSession($newAjoutSession);

        //     // $entityManager = $this->getDoctrine()->getManager();
        //     // $entityManager->persist($stagiaire);
        //     $em->flush();


        //     return $this->redirectToRoute('showOne_stagiaire', array('id' => $stagiaire->getId()));
        // }

        // return $this->render('stagiaire/ajoutSessionForm.html.twig', [
        //     'ajoutsessionform' => $form->createView(),
        //     'stagiaire' => $stagiaire
        // ]);
     }


     
    /**
     * @Route("/edit/{id}", name="edit_stagiaire")
     */
    public function editStagiaire(Stagiaire $stagiaire, Request $request, EntityManagerInterface $entityManager){

        $form = $this->createForm(StagiaireFormType::class, $stagiaire);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            $this->addFlash("success", "Le stagiaire a bien été modifié");
            return $this->redirectToRoute("stagiaire_index");
        }
        
        return $this->render('stagiaire/stagiaireForm.html.twig', [
            "stagiaire_form" => $form->createView()
        ]);
    }
     

    /**
    * @Route("/{id}/removesession/{id_session}", name="remove_one_session_from_stagiaire")
    */
    public function removeOneSessionFromStagiaire(Stagiaire $stagiaire, Request $request){
        //On récupère l'ID de la session concerné passé en GET 
        $id = $request->attributes->get('id_session');
        //Grâce à l'EntityManager de Symfony, on récupère l'objet Session à partir de son ID
        $entityManager = $this->getDoctrine()->getManager();
        $session = $this->getDoctrine()->getRepository(Session::class)->find($id);
        //On supprime l'entrée Session du tableau lié à ce stagiaire
        $stagiaire->removeSession($session);
        $entityManager->flush();

        //On redirige vers la page du stagiaire avec un joli message
        $this->addFlash("success", "La session a bien été supprimé !");
    return $this->redirectToRoute("showOne_stagiaire", array('id' => $stagiaire->getId()));
 }

    /**
    * @Route("/delete/{id}", name="remove_one_stagiaire")
    * @IsGranted("ROLE_ADMIN")
    */
    public function removeOnestagiaire(Stagiaire $stagiaire, EntityManagerInterface $entityManager){
        //On appelle la fonction remove propre à Symfony.
        //On lui passe l'objet Stagiaire courant
        //On actualise la BDD
        $entityManager->remove($stagiaire);
        $entityManager->flush();

    //On redirige vers la liste des stagiaires
    return $this->redirectToRoute("stagiaire_index");
 }

    /**
    * @Route("/{id}", name="showOne_stagiaire")
    */
    public function showOne(Stagiaire $stagiaire){

        
        return $this->render('stagiaire/showOne.html.twig', ['stagiaire' => $stagiaire]
        );
    }
}
