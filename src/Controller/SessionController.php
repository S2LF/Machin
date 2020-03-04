<?php

namespace App\Controller;


use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Contenir;
use App\Entity\Stagiaire;
use App\Form\SessionFormType;
use App\Form\AjoutModuleFormType;
use App\Form\AjoutStagiaireFormType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/session")
 * @IsGranted("ROLE_USER")
 */
class SessionController extends AbstractController
{
    /**
     * @Route("/", name="session_index")
     */
    public function index()
    {

        $sessions = $this->getDoctrine()
                        ->getRepository(Session::class)
                        ->getAll();

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    /**
    * @Route("/new", name="form_session")
    */
    public function newForm(Request $request){


        $newSession = new Session();
        $form = $this->createForm(SessionFormType::class, $newSession);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $newSession = $form->getData();

            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newSession);
            $entityManager->flush();

            $this->addFlash("success", "La session a bien été crée !");
            return $this->redirectToRoute('session_index');
        }
        return $this->render('session/sessionForm.html.twig', [
        'session_form' => $form->createView()
        ]);
     }


    /**
    * @Route("/newModule/{id}", name="ajout_module")
    */
    public function AjoutModuleForm(Session $session, Request $request){


        $newAjoutModule= new Contenir();
        $newAjoutModule->setSession($session);

        $form = $this->createForm(AjoutModuleFormType::class, $newAjoutModule);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $newAjoutModule = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            try{
                $entityManager->persist($newAjoutModule);
                $entityManager->flush(); 
                $this->addFlash("success", "Le module a bien été ajouté !");
                return $this->redirectToRoute("show_one_session", array('id' => $session->getId()));
            }
            catch( UniqueConstraintViolationException $e){
                $this->addFlash("error", "Le module existe déjà dans la Session");
            }

           
            
        }

        return $this->render('session/ajoutModuleForm.html.twig', [
            'ajoutmoduleform' => $form->createView(),
            'session' => $session
        ]);
     }


     /**
    * @Route("/newStagiaire/{id}", name="ajout_stagiaire")
    */
    public function AjoutStagiaireForm(Session $session, Request $request, EntityManagerInterface $em){


    // Liste déroulante adapté
        $stagiaires = $em->getRepository(Stagiaire::class)->findAll();

        foreach($stagiaires as $key => $stagiaire){
            if($session->getStagiaires()->contains($stagiaire)){
                unset($stagiaires[$key]);
            }
        }

        if($stagiaire_id = $request->request->get('stagiaire')){
            $stagiaire = $em->getRepository(Stagiaire::class)->findOneBy(['id' => $stagiaire_id]);

            $session->addStagiaire($stagiaire);
           
            $em->flush();
            $this->addFlash("success", "Stagiaire bien ajouté !");
            return $this->redirectToRoute("show_one_session", array('id' => $session->getId()));
        }
        
        return $this->render('session/ajoutStagiaireForm.html.twig', [
            'stagiairesDispo' => $stagiaires,
            //"ajout_stagiaire_form" => $form->createView(),
            'session' => $session
        ]);



        // Formulaire Symfony
        //  $form = $this->createForm(AjoutStagiaireFormType::class, $session);

        //  $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
            
        //     $newAjoutStagiaire = $form->get('stagiaire')->getData();

        //     $session->addStagiaire($newAjoutStagiaire);

        //     $em->flush();

        //     
        //     
        // }

       
     }


    /**
     * @Route("/edit/{id}", name="edit_session")
     */
    public function editSession(Session $session, Request $request, EntityManagerInterface $entityManager){

        $form = $this->createForm(SessionFormType::class, $session);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            $this->addFlash("success", "La session a bien été modifié !");
            return $this->redirectToRoute("session_index");
        }
        
        return $this->render('session/sessionForm.html.twig', [
            "session_form" => $form->createView()
        ]);
    }

    /**
    * @Route("/delete/{id}", name="remove_one_session")
    */
    public function removeOneSession(Session $session, EntityManagerInterface $entityManager){

        $entityManager->remove($session);
        $entityManager->flush();

        $this->addFlash("success", "La session a été supprimé avec succès !");
    return $this->redirectToRoute("session_index");
 }

        /**
    * @Route("/{id}/removemodule/{id_contenir}", name="remove_one_module_from_session")
    */
    public function removeOneModuleFromSession(Session $session, Request $request){

        $id = $request->attributes->get('id_contenir');
        $entityManager = $this->getDoctrine()->getManager();
        $contenir = $this->getDoctrine()->getRepository(Contenir::class)->findOneBy([
            'session'=> $session->getId(),
            'module'=> $id
        ]);
        $entityManager->remove($contenir);
        $entityManager->flush();

        $this->addFlash("success", "Le module a bien été supprimé de la session !");
    return $this->redirectToRoute("show_one_session", array('id' => $session->getId()));
 }

    /**
    * @Route("/{id}/removestagiaire/{id_stagiaire}", name="remove_one_stagiaire_from_session")
    */
     public function removeOneStagiaireFromSession(Session $session, Request $request){

            $id = $request->attributes->get('id_stagiaire');
            $entityManager = $this->getDoctrine()->getManager();
            $stagiaire = $this->getDoctrine()->getRepository(Stagiaire::class)->find($id);
            $session->removeStagiaire($stagiaire);
            $entityManager->flush();

            $this->addFlash("success", "Le stagiaire a bien été supprimé de la session !");
        return $this->redirectToRoute("show_one_session", array('id' => $session->getId()));
     }


    /**
     * @Route("/{id}", name="show_one_session", methods="GET")
     */
     public function showOne(Session $session){

         return $this->render('session/showOne.html.twig', [
             'session' => $session ]);

     }

    /**
     * @Route("/valid/ajax", name="ajax_validation", methods={"GET"})
     */
     public function ajax_validation(Request $request, EntityManagerInterface $em){

        $sessid = $request->query->get("sessid");

        $session = $em->getRepository(Session::class)->findOneBy(['id' => $sessid]);

        $html = $this->renderView("session/ajax.html.twig", [
            "session" => $session
        ]);

        return new Response($html);

     }

}
