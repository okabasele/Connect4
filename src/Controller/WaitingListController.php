<?php

namespace App\Controller;

use App\DataPersister\WaitingListDataPersister;
use App\Entity\WaitingList;
use App\Form\WaitingListType;
use App\Repository\WaitingListRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WaitingListController extends AbstractController
{
    //ADD NEW WaitingList TO BDD AND CHOOSE MODE
    #[Route('/new/waitinglist', name:'new_wlist')]
    public function index(Request $request, EntityManagerInterface $manager, RequestStack $rs,WaitingListRepository $repo): Response
    {
        //recup wlist
        $all = $repo->findAll();

        //recup player co
        $player = $rs->getSession()->get('player');
        //verifier qu'on est pas deja dans la liste
        foreach ($all as $waiting) {
            if ($waiting->getPlayer() === $player) {
                $repo->remove($waiting);
            }
        }

        //Nouveau joueur dans le tableau "WaitingList"
        $wlist = new WaitingList;
        $wlist->setEnteredAt(new DateTime())
        ->setPlayer($player);
        
        $manager->persist($wlist);
        $manager->flush();

        // return new Response($wlist);
        //redirection au menu
        return $this->redirectToRoute('game_duo');
        
    }

    
}