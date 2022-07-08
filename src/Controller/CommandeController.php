<?php

namespace App\Controller;

use DateTime;
use App\Entity\Commande;
use App\Entity\CommandeDetail;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommandeDetailRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(): Response
    {
        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
        ]);
    }

    /**
     * @Route("/passer-ma-commande", name="passer_commande")
     */

    public function passerCommande(SessionInterface $session, ProduitRepository $repoPro, CommandeRepository $repoCom, CommandeDetailRepository $repoDet,
    EntityManagerInterface $manager,)
    {
        // on crée un objet commande pour remplir les informations
        $commande = new Commande();

        $panier = $session->get('panier', []);

       // dd($panier);
       // on recupere l'utilisateur en cours

       $user = $this->getUser();

       // s'il nya pas d'utilisateur en cours(connecté) il ne peut pas passer une commande
       if(!$user)
       {
          $this->addFlash("error", "Veuillez vous connecter, ou vous inscrire dans le cas echeant,pour pouvoir passee votre
          commande");

          return $this->redirectToRoute("app_login");
       }

       if(empty($panier))
       {
        $this->addFlash("error", "Votre panier est vide, vous ne pouvez pas passer de commande !!");
        return $this->redirectToRoute("produits_all");
       }


        $dataPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {

            $produit = $repoPro->find($id);
            $dataPanier[]=
            [
                "produit" => $produit,
                "quantite" => $quantite,
                "sousTotal" =>$produit->getPrix() * $quantite
            ];

            $total += $produit->getPrix() * $quantite;


        }

        // dd($dataPanier);
        
        $commande->setUser($user)
                 ->setDateDeCommande(new DateTime("now"))
                 ->setMontant($total)
                 
            ;

        $repoCom->add($commande); 

        foreach ($dataPanier as $key => $value) {

            $commandeDetail = new CommandeDetail();

            $produit = $value["produit"];
            $quantite = $value["quantite"];
            $sousTotal = $value["sousTotal"];

            $commandeDetail->setCommande($commande)
                            ->setProduit($produit)
                            ->setQuantite($quantite)
                            ->setprix($sousTotal)
                            
            ;

            $repoDet->add($commandeDetail);

        }

           
        $manager->flush();

        // une fois la commande passée on supprime le panier
        $session->remove("panier");

        this->addFlash("success", "felicitation, votre commande a bien été enregistré !");

        return $this->redirectToRoute("app_home");

        

    }       

           



            
                    
 }


