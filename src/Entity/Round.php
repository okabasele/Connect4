<?php

namespace App\Entity;

use App\Repository\RoundRepository;
use ConnectFour\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
class Round
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'rounds')]
    #[ORM\JoinColumn(nullable: false)]
    private $game;

    #[ORM\Column(type: 'text')]
    private $move;

    #[ORM\Column(type: 'string', length: 255)]
    private $player;


    public function __construct(Game $game, int $column, int $row, Player $player)
    {
        $this->game = $game;
        $this->player = $player->getUsername();
        $this->move = "Add disc on row ($row) in the column ($column)";
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getMove(): ?string
    {
        return $this->move;
    }

    public function setMove(string $move): self
    {
        $this->move = $move;

        return $this;
    }

    public function getPlayer(): ?string
    {
        return $this->player;
    }

    public function setPlayer(string $player): self
    {
        $this->player = $player;

        return $this;
    }
}
