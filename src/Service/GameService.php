<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\Player;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class GameService
{
    //Injection de dépendances hors d'un controller 
    private $rs;
    private $repo;
    private $player_repo;
    private $games;

    public function __construct(RequestStack $rs, PlayerRepository $playerRepo, GameRepository $repo)
    {
        $this->rs = $rs;
        $this->player_repo = $playerRepo;
        $this->repo = $repo;
        $this->games = [];
    }

    public function isUserAllowedIn(): bool
    {
        //Recuperer le joueur dans la session
        $session = $this->rs->getSession();
        $player = $session->get('player');
        if ($player != null) {
            return true;
        }
        return false;
    }

    public function getPlayer(): ?Player
    {
        $session = $this->rs->getSession();
        $player = $session->get('player');
        return $player;
    }

    public function addGameToSession(Game $game) :void
    {
        array_push($this->games, $game);
    }

    public function getCurrentGame(): ?Game
    {
        $session = $this->rs->getSession();
        // $found_key = array_search($game_id)
        $game = $session->get('game');
        return $game;
    }

    public function getAIPlayer(): Player
    {
        $player = $this->player_repo->find(0);
        return $player;
    }

    public function getRepository()
    {
        return $this->repo;
    }
}
