<?php

namespace ConnectFour;

use App\Entity\Game;

class Player
{

    private $username;
    private $color;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getColor() : string
    {
        return $this->color;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
    }

    public function dropDisc(Game $game, int $column, bool $addMove = true)
    {
        $currentPlayer = $game->getCurrentPlayer();

        $game->addDisc($column, $this, $addMove);
    }

    public function __toString()
    {
        return sprintf('Player(%s,%s)', $this->username,$this->color);
    }
}