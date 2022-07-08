<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/passer-en-admin_{id<\d+>}"), name="passer_en_admin
     */
    public function passerEnAdmin ($id, UserRepository $repo, Request $request) 
    {
        $secret = "12345";

        $form = $this->CreateForm(AdminType::class);
        $form->handleRequest($request);

        //on recupere  l'utulisateur dont l'id est celui passé dans l'url

        $user = $repo->find($id);

        if (!$user) {
            $this->addFlash("error", "aucun utilisateur trouvé avec l'id $id");
            return $this->redirectToRoute("app_home");
        }

        if($form->isSubmitted() && $form->isValid())
         {

                //si la saisie dans le champ "secret" du formulaire correspond au mdp stocké dans la variable $secret

            if($form->get('secret')->getData() == $secret ) {

                $user->setRoles(["ROLE_ADMIN"]);
            }else{
                $this->addFlash("error", "vous n'avez pas les droits pour affecter cette action, veuillez contacter l'administrateur du site !");
                return $this->redirectToRoute("app_home");
            }


            // en passant par la méthode add du repository,l'objet sera parsisté et envoyé en bdd gràce au parametre 1 (true)
            $repo->add($user, 1);

            $this->addFlash("success", "vous etes désormais Admin,veuillez vous reconnecter pour profiter de vos privilegiés");
            return $this->redirectToRoute("app_home");
        }

        return $this->render ("security/passerEnAdmin.html.twig", [
            "user"=> $user,
            "formAdmin" => $form->createView()
        ]);
    }
}

