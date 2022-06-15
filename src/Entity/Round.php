<?php

namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Repository\RoundRepository;
use App\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
#[ApiResource(
    [
        'normalizationContext' => ['groups' => 'round:read'],
        'denormalizationContext' => ['groups' => 'round:write']
    ],
    paginationEnabled: false,
)]
class Round
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['round:read'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'rounds')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['round:read','round:write'])]
    private $game;

    #[ORM\Column(type: 'text')]
    #[Groups(['round:read','round:write'])]
    private $move;

    // #[ORM\Column(type: 'string', length: 255)]
    #[ORM\ManyToOne(targetEntity: Player::class,cascade:["persist"])]
    #[ORM\JoinColumn(name:"player_id", referencedColumnName:"id")]
    #[Groups(['round:read','round:write'])]
    private $player;

    #[ORM\Column(type: 'integer',name:'col')]
    private $column;
    private $row;


    public function __construct(Game $game, int $column, int $row, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
        $this->move = "Add disc on row ($row) in the column ($column)";
        $this->column = $column;
        $this->row = $row;
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

    public function getColumn(): ?int
    {
        return $this->column;
    }

    public function setColumn(int $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): self
    {
        $this->row = $row;

        return $this;
    }


    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function __toString(){
        // return sprintf("ID = %d ; GameID = %d ; Player = %s ; Move = %s",$this->getId(),$this->getGame()->getId(), $this->getPlayer(),$this->getMove());
        $res = json_encode([
            'id' => $this->id,
            'game' => $this->game,
            'move' => $this->move,
            'playerMove' => $this->player
        ]);
        $array= json_decode($res,true);
        return json_encode($array);;
    }
    
}
