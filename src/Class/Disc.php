<?php

namespace ConnectFour;

use App\Entity\Player;

class Disc
{
    private $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer() : Player
    {
        return $this->player;
    }
}