<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $player_one;

    #[ORM\Column(type: 'string', length: 255)]
    private $player_two;

    #[ORM\Column(type: 'text')]
    private $board;

    #[ORM\Column(type: 'string', length: 255)]
    private $status;

    #[ORM\Column(type: 'string', length: 255)]
    private $result;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerOne(): ?string
    {
        return $this->player_one;
    }

    public function setPlayerOne(string $player_one): self
    {
        $this->player_one = $player_one;

        return $this;
    }

    public function getPlayerTwo(): ?string
    {
        return $this->player_two;
    }

    public function setPlayerTwo(string $player_two): self
    {
        $this->player_two = $player_two;

        return $this;
    }

    public function getBoard(): ?string
    {
        return $this->board;
    }

    public function setBoard(string $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }
}
