<?php

namespace App\Controller;

use App\DataPersister\GameDataPersister;
use App\DataPersister\RoundDataPersister;
use App\Entity\Game;
use App\Entity\Player;
use App\Entity\WaitingList;
use App\Form\PlayerType;
use App\Repository\WaitingListRepository;
use App\Service\GameService;
use ConnectFour\GameFinishedException;
use ConnectFour\NotYourTurnException;
use ConnectFour\OutOfBoardException;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    //ADD NEW PLAYER TO BDD AND CHOOSE MODE
    #[Route('/', name: 'app_home')]
    public function index(GameService $gs): Response
    {
        //verification access
        if ($gs->isUserAllowedIn()) {
            //redirection au menu
            return $this->redirectToRoute('app_menu');
        }
        // //Nouveau joueur dans le tableau "Player"
        $player = new Player;
        $form = $this->createForm(PlayerType::class, $player, [
            'action' => $this->generateUrl('new_player'),
            'method' => 'POST',
        ]);

        //Moteur de rendu
        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //MENU
    #[Route('/menu', name: 'app_menu')]
    public function menu(GameService $gs): Response
    {
        //verification access
        if (!$gs->isUserAllowedIn()) {
            //redirection à l'accueil
            return $this->redirectToRoute('app_home');
        }

        //Moteur de rendu
        return $this->render('home/menu.html.twig', []);
    }

    //SOLO PLAY
    #[Route('/menu/solo', name: 'game_solo')]
    public function solo(GameService $gs): Response
    {
        //verification access
        if (!$gs->isUserAllowedIn()) {
            //redirection à l'accueil
            return $this->redirectToRoute('app_home');
        }

        //Créer une partie avec une IA

        //Moteur de rendu
        return $this->render('home/solo.html.twig', [
            'player' => $gs->getPlayer(),
            'ai' => $gs->getAIPlayer()
        ]);
    }

    //DUO PLAY
    #[Route('/menu/duo', name: 'game_duo')]
    public function duo(Request $request, GameService $gs, EntityManagerInterface $manager, WaitingListRepository $repo,HubInterface $mercureHub): Response
    {

        //verification access
        if (!$gs->isUserAllowedIn()) {
            //redirection à l'accueil
            return $this->redirectToRoute('app_home');
        }

        //recup les parties
        $games = $gs->getRepository()->findAll();

        //recup player co
        $player = $gs->getPlayer();

        //Verifier dans la bdd s'il n'y a pas de partie disponible
        if (sizeof($games) > 0 && end($games)->getPlayerTwo() == null) { // si y'a une partie en attente, on l'a rejoins
            //supp player de waiting list
            //recup wlist
            $all = $repo->findAll();

            //verifier qu'on est pas deja dans la liste d'attente
            foreach ($all as $waiting) {
                if ($waiting->getPlayer() === $player) {
                    $repo->remove($waiting);
                }
            }

            //ajouter player a la partie en cours
            $toJoin = end($games);
            $toJoin->addPlayer($player);
            $manager->persist($toJoin);    // préparer la modification du jeu en bdd
            $manager->flush();

            if ($toJoin->getGameStatus() == Game::PLAYING) {
                $update = new Update(
                    'games+',
                    'start'
                );
               $mercureHub->publish($update);

            }
            return $this->redirectToRoute('viewGame', array(
                'id' => $toJoin->getId(),
                'player' => 2,
            ));
        } elseif (sizeof($games) > 0 && end($games)->getResult() == "") { //si la partie est en cours on attends
            //ajoue dans la liste d'attente
            //recup wlist
            $all = $repo->findAll();

            //verifier qu'on est pas deja dans la liste d'attente
            foreach ($all as $waiting) {
                if ($waiting->getPlayer() === $player) {
                    return $this->redirectToRoute('game_duo');
                }
            }

            //Nouveau joueur dans le tableau "WaitingList"
            $wlist = new WaitingList;
            $wlist->setEnteredAt(new DateTime())
                ->setPlayer($player);

            //on ajoute l'utilisateur connecté dans la liste d'attente
            $manager->persist($wlist);
            $manager->flush();

            // return $this->redirectToRoute('new_wlist');

        } elseif (sizeof($games) == 0 || sizeof($games) > 0 && end($games)->getResult() != "") { //si ya pas de partie dispo ou en cours, on crée une partie
            //supp player de waiting list
            //recup wlist
            $all = $repo->findAll();

            //verifier qu'on est pas deja dans la liste d'attente
            foreach ($all as $waiting) {
                if ($waiting->getPlayer() === $player) {
                    $repo->remove($waiting);
                }
            }

            // return $this->redirectToRoute('new_game',['menu_choice'=>2]);
            return $this->redirectToRoute('new_game', [
                'request' => $request
            ], 307);
        }

        //Moteur de rendu
        return $this->render('home/duo.html.twig', []);
    }

    //ADD NEW Game TO BDD
    #[Route('/new/game', name: 'new_game', methods: 'POST')]
    public function newGame(Request $request, GameDataPersister $manager,  GameService $gs, HubInterface $mercureHub): Response
    {

        //recup joueur
        $player = $gs->getPlayer();

        //recuperer les données envoyées
        $iterator = $request->request->getIterator();
        $data = $iterator->getArrayCopy();

        //Nouvelle partie
        $game = new Game;
        $game->setBoard("")
            ->setResult("")
            ->setStatus("");

        //une partie solo
        if (isset($data["menu_choice"]) && $data["menu_choice"] == 1) {
            $player->setColor($data["player_one_color"]);
            $game->addPlayer($player);
            $ai = $gs->getAIPlayer();
            $ai->setColor($data["player_two_color"]);
            $game->addPlayer($ai);
        } else { //duo
            $game->addPlayer($player);
        }

        // dd($game);
        
        //ajouter dans la bdd
        $manager->persist($game);

        //Mercure publish
        $update = new Update(
            'games',
            $game
        );
        $mercureHub->publish($update);


        //redirection au jeu 
        return $this->redirectToRoute('viewGame', array(
            'id' => $game->getId(),
            'player' => 1,
        ));
    }

    #[Route('/game/{id}/{player}', name: 'viewGame')]
    public function viewAction(Game $game, GameService $gs, Request $request)
    {
        // dd($game);
        $playerID = $request->attributes->get('player');
        //verification access
        if ((!$gs->isUserAllowedIn())) {
            //redirection à l'accueil
            return $this->redirectToRoute('app_home');
            // dd($player,$game->getPlayerOne(),$gs->getPlayer(),$game);
        }
        // TODO : add session check for player nickname
        $game->replayMoves();
        $view = "";
        switch ($game->getGameStatus()) {
            case Game::FINISHED:
                $view = 'finished';
                break;
            case Game::WAITING:
                $view = 'waiting';
                break;
            case Game::PLAYING:
                // return new Response($gs->getPlayer());
                if ($game->getCurrentPlayer()->getId() == $gs->getPlayer()->getId()) {
                    $view = 'playing';
                } else {
                    $view = 'notyourturn';
                }
                break;
            default:
                //TODO : handle errors
        }

        return $this->render("game/$view.html.twig", array(
            'game' => $game,
            'playerID' => $playerID
        ));
    }

    // Views a game at its last step.
    #[Route('/game/{id}/drop/{column}/{player}', name: 'dropDisc')]
    public function dropDiscAction(Game $game,int $column, GameService $gs, HubInterface $mercureHub, EntityManagerInterface $manager, Request $request)
    {
        $game->replayMoves();
        $playerID = $request->attributes->get('player');
        // dd($column);
        //verification access
        if (!$gs->isUserAllowedIn()) {
            //redirection à l'accueil
            return $this->redirectToRoute('app_home');
        }
        $player = $game->getCurrentPlayer();
        // TODO : add session check for player nickname

        try {
            $player->dropDisc($game, $column);
            $manager->persist($game);
        
            $manager->flush();

            //Mercure publish
            $update = new Update(
                'rounds+',
                $player
            );
            $mercureHub->publish($update);
        } catch (GameFinishedException $gfe) {
            $this->addFlash(
                'notice',
                'Game is finished, you cannot play anymore.'
            );
        } catch (NotYourTurnException $nyte) {
            $this->addFlash(
                'notice',
                'It\'s your opponent\'s turn.'
            );
        } catch (OutOfBoardException $oobe) {
            $this->addFlash(
                'notice',
                'Cannot play outside of game board.'
            );
        } catch (\Exception $e) {
            dd($e);
            $this->addFlash(
                'notice',
                sprintf('Something happened : %s', $e->getMessage())
            );
        }

        return $this->redirectToRoute('viewGame', array(
            'id' => $game->getId(),
            'player' => $playerID
        ));
    }
}
