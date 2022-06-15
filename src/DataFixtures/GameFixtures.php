<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\WaitingList;
use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $player_one = new Player("pomme");
        $manager->persist($player_one);

        $player_two = new Player("tomate");
        // $player_two = new WaitingList;
        // $player_two->setEnteredAt(new \DateTime());
        // $player_two->setUsername("Player 2");
        $manager->persist($player_two);

        $game = new Game();
        $game->addPlayer($player_one);
        $game->addPlayer($player_two);

        $game->getCurrentPlayer()->dropDisc($game, 4);
        $game->getCurrentPlayer()->dropDisc($game, 5);
        $game->getCurrentPlayer()->dropDisc($game, 4);
        $game->getCurrentPlayer()->dropDisc($game, 5);
        $game->getCurrentPlayer()->dropDisc($game, 6);
        $game->getCurrentPlayer()->dropDisc($game, 5);
        $game->getCurrentPlayer()->dropDisc($game, 4);
        $game->getCurrentPlayer()->dropDisc($game, 6);
        $game->getCurrentPlayer()->dropDisc($game, 4);
        // $game->getCurrentPlayer()->dropDisc($game, 2);
        // $game->getCurrentPlayer()->dropDisc($game, 4);
        // $game->getCurrentPlayer()->dropDisc($game, 5);

        $manager->persist($game);

        $manager->flush();
    }
}
