<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/panier", name="panier_")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/show", name="show")
     */
    public function show(SessionInterface $session, ProduitRepository $repo): Response
    {
        $panier = $session->get("panier", []);

        $dataPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $produit = $repo->find($id);
            $dataPanier[] = [
                "produit" => $produit,
                "quantite" => $quantity
            ];

            $total += $produit->getPrix() * $quantity;
        }
        //dd($dataPanier);
        return $this->render('/panier/index.html.twig', [
            'dataPanier' => $dataPanier,
            'total' => $total
        ]);
    }


    /**
     * @Route("/add/{id<\d+>}", name="add")
     */
    public function add($id, SessionInterface $session)
    {
        // on récupere ou on crée le panier dans la session
        $panier = $session->get('panier', []);

        // on verifie si l'id existe déjà, dans ce cas on incremente sinon on le crée
        if (empty( $panier[$id] ))
        {
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }
         //on sauvegarde dans la session
        $session->set("panier", $panier);

        //dd($session->get("panier"));
        return $this->redirectToRoute("panier_show");

    }

    /**
     * @Route("delete/{id<\d+>}", name="delete_produit")
     */
    public function delete($id, SessionInterface $session)
    { 
        $panier = $session->get("panier", []);

        if(!empty( $panier[$id]))
        {

            unset($panier[$id]);
        }else{
            $this->addFlash("error", "Le produit que vous essayer de retirer du panier n'existe pas !!!");

            return $this->redirectToRoute("panier_show");
        }
        
        $session->set("panier", $panier);

        $this->addFlash("success", "Le produit a bien été retiré du panier!");
         return $this->redirectToRoute("panier_show");
    
    }  
    


        
}
    
       




    

    

