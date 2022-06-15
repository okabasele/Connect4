<?php

namespace App\Controller;

use App\DataPersister\PlayerDataPersister;
use App\DataProvider\PlayerDataProvider;
use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    //ADD NEW PLAYER TO BDD AND CHOOSE MODE
    #[Route('/new/player', name: 'new_player', methods: 'POST')]
    public function index(Request $request, EntityManagerInterface $manager, RequestStack $rs, HubInterface $mercureHub): Response
    {

        //recuperer les données envoyées
        $data = $request->request->get('player');

        //Nouveau joueur dans le tableau "Player"
        $player = new Player;
        $player->setUsername($data["username"]);
        $manager->persist($player);
        $manager->flush();

        //Mercure publish
        $update = new Update(
            'players',
            $player
        );
        $mercureHub->publish($update);

        //Enregistrer le joueur dans la session
        $session = $rs->getSession();
        $session->set('player', $player);

        //redirection au menu
        return $this->redirectToRoute('app_menu');
    }
}
