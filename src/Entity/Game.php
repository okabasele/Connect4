<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;

use App\Repository\GameRepository;
use ConnectFour\Board;
use ConnectFour\DiscNotFoundException;
use ConnectFour\GameFinishedException;
use ConnectFour\NotYourTurnException;
use ConnectFour\OutOfBoardException;
use App\Entity\Player;
use ConnectFour\TooManyPlayersException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]

#[ApiResource(
    [
        'normalizationContext' => ['groups' => 'game:read'],
        'denormalizationContext' => ['groups' => 'game:write']
    ],
    paginationEnabled: false,
    mercure: true
)]

class Game
{
    const WAITING = 0;
    const PLAYING = 1;
    const FINISHED = 2;

    const FIRST_PLAYER_COLOR = 'yellow';
    const SECOND_PLAYER_COLOR = 'red';

    private $boardClass;
    private $finished;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'games', cascade: ["persist"])]
    #[ORM\JoinColumn(name: "starting_player_id", referencedColumnName: "id")]
    private $startingPlayer;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'games', cascade: ["persist"])]
    #[ORM\JoinColumn(name: "current_player_id", referencedColumnName: "id")]
    private $currentPlayer;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['game:read'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'games', cascade: ["persist"])]
    #[ORM\JoinColumn(name: "player_one_id", referencedColumnName: "id")]
    #[Groups(['game:read', 'game:write'])]
    private $player_one;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'games', cascade: ["persist"])]
    #[ORM\JoinColumn(name: "player_two_id", referencedColumnName: "id")]
    #[Groups(['game:read', 'game:write'])]
    private $player_two;

    #[ORM\Column(type: 'text')]
    #[Groups(['game:read', 'game:write'])]
    private $board;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['game:read', 'game:write'])]
    private $status;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['game:read', 'game:write'])]
    private $result;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Round::class, orphanRemoval: true, cascade: ["all"])]
    #[Groups(['game:read'])]
    private $rounds;

    public function __construct()
    {
        $this->boardClass = new Board();
        $this->rounds = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerOne(): ?Player
    {
        return $this->player_one;
    }

    public function setPlayerOne(?Player $player_one): self
    {
        $this->player_one = $player_one;

        return $this;
    }

    public function getPlayerTwo(): ?Player
    {
        return $this->player_two;
    }

    public function setPlayerTwo(?Player $player_two): self
    {
        $this->player_two = $player_two;

        return $this;
    }

    public function getBoard(): ?string
    {
        return $this->board;
    }

    public function getBoardClass()
    {
        return $this->boardClass;
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

    /**
     * @return Collection<int, Round>
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): self
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds[] = $round;
            $round->setGame($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getGame() === $this) {
                $round->setGame(null);
            }
        }

        return $this;
    }

    public function getCurrentPlayer(): Player
    {
        return $this->currentPlayer;
    }

    public function getGameStatus(): string
    {
        if ($this->isFinished()) {
            return self::FINISHED;
        } elseif ((bool) $this->player_one && (bool) $this->player_two) {
            return self::PLAYING;
        }
        return self::WAITING;
    }

    public function isFinished() // : bool
    {
        return $this->finished;
    }

    public function addPlayer(Player $player)
    {
        if (!$this->player_one) {
                $player->setColor(self::FIRST_PLAYER_COLOR);
            $this->player_one = $player;
        } elseif (!$this->player_two) {
                $player->setColor(self::SECOND_PLAYER_COLOR);
            
            $this->player_two = $player;

            // here both players are assigned, we can decide who starts
            $this->startingPlayer = rand(0, 1) == 0 ? $this->player_one : $this->player_two;
            $this->currentPlayer = $this->startingPlayer;
        } else {
            throw new TooManyPlayersException();
        }
    }

    public function addDisc(int $column, Player $player, bool $addMove = true)
    {
        if ($this->isFinished()) {
            throw new GameFinishedException();
        }
        if ($this->currentPlayer != $player) {
            throw new NotYourTurnException();
        }

        $rowAdded = $this->getBoardClass()->addDisc($column, $player);

        if ($addMove) {
            $round = new Round($this, $column, $rowAdded, $player);
            $this->addRound($round);

           
        }

        if ($this->isDiscWinner($column, $rowAdded)) {
            $this->winner = $player;
            $this->finished = true;
            $this->setStatus("FINISHED");
            $this->setResult($player->getUsername() . " won.");
            $this->board = $this->getBoardClass()->getCellsToString($this);

            return;
        }

        if ($this->getBoardClass()->isFull()) {
            $this->finished = true;
            $this->setStatus("FINISHED");
            $this->setResult("TIE");

            return;
        }

        $this->switchPlayer();
    }

    //On change de joueur
    private function switchPlayer()
    {
        $this->currentPlayer = ($this->player_one == $this->currentPlayer) ? $this->player_two : $this->player_one;
    }

    //ia qui joue
    public function aiPlaying(){
        if ($this->currentPlayer->getId() == 0) {
            if ($this->getRounds()->count()==0) {
                $this->currentPlayer->dropDisc($this, 0, true); 
            } else {
                $lastRound = $this->getRounds()->last();
                // $rand = rand(0,1);
                $column = $lastRound->getColumn();
                $row = $lastRound->getRow();
                if ($row + 1 < 6) {
                    $column = $lastRound->getColumn();
                } elseif ($column - 1 >= 0) {
                    $column = $column - 1;
                } elseif ($column + 1 < 7) {
                    $column = $column + 1;
                }
                $this->currentPlayer->dropDisc($this, $column, true);
            }
        }
    }

    //Verifie s'il y a un puissance 4
    private function isDiscWinner(int $column, int $row)
    {
        $player = $this->getBoardClass()->getDisc($column, $row)->getPlayer();
        $horizontalCount = 1 + $this->countConsecutiveDiscsAsideOf($column, $row, $player, -1, 0)
            + $this->countConsecutiveDiscsAsideOf($column, $row, $player, +1, 0);
        if ($horizontalCount >= 4) {
            return true;
        }

        $verticalCount = 1 + $this->countConsecutiveDiscsAsideOf($column, $row, $player, 0, -1)
            + $this->countConsecutiveDiscsAsideOf($column, $row, $player, 0, +1);
        if ($verticalCount >= 4) {
            return true;
        }

        $topLeftBottomRightCount = 1 + $this->countConsecutiveDiscsAsideOf($column, $row, $player, -1, -1)
            + $this->countConsecutiveDiscsAsideOf($column, $row, $player, +1, +1);
        if ($topLeftBottomRightCount >= 4) {
            return true;
        }

        $bottomLeftTopRightCount = 1 + $this->countConsecutiveDiscsAsideOf($column, $row, $player, -1, +1)
            + $this->countConsecutiveDiscsAsideOf($column, $row, $player, +1, -1);
        if ($bottomLeftTopRightCount >= 4) {
            return true;
        }

        return false;
    }

    //  Scans a side of a position (exclusive) by horizontal and vertical steps.
    private function countConsecutiveDiscsAsideOf(int $column, int $row, Player $player, int $columnStep = 1, int $rowStep = 1)
    {
        $cnt = 0;
        $maxPositions = 3;
        $x = $column + $columnStep;
        $y = $row + $rowStep;
        while (
            $this->isHorizontallyInBounds($x, $column, $columnStep, $maxPositions) &&
            $this->isVerticallyInBounds($y, $row, $rowStep, $maxPositions)
        ) {
            try {
                try {
                    $disc = $this->getBoardClass()->getDisc($x, $y, $player);
                } catch (DiscNotFoundException $dnfe) {
                    break;
                }
                ++$cnt;
                $x += $columnStep;
                $y += $rowStep;
            } catch (OutOfBoardException $e) {
                // if we're being out of the board, stop looking for discs
                break;
            }
        }

        return $cnt;
    }

    // check limit horizontal
    private function isHorizontallyInBounds($x, $column, $columnStep, $maxPositions)
    {
        if ($columnStep < 0) {
            return $x >= $column + ($maxPositions * $columnStep);
        } else {
            return $x <= $column + ($maxPositions * $columnStep);
        }
    }

    //check limit vertical
    private function isVerticallyInBounds($y, $row, $rowStep, $maxPositions)
    {
        if ($rowStep < 0) {
            return $y >= $row + ($maxPositions * $rowStep);
        } else {
            return $y <= $row + ($maxPositions * $rowStep);
        }
    }


    //get player color
    public function getPlayerColor(Player $player): string
    {
        return $player->getColor();
    }

    public function replayMoves()
    {
        $this->boardClass = new Board();
        $this->currentPlayer = $this->startingPlayer;
        foreach ($this->rounds as $round) {
            $round->getPlayer()->dropDisc($this, $round->getColumn(), false);
        }
    }

    public function __toString()
    {
        // return "id :" . $this->id . " / playerOne :" . $this->player_one . " / playerTwo :" . $this->player_two . " / status :" . $this->status . " / result:" . $this->result." / currentPlayer".$this->currentPlayer." / startingPlayer".$this->startingPlayer;
        $res = json_encode([
            'id' => $this->id,
            'player_one' => $this->player_one,
            'player_two' => $this->player_two,
            'board' => $this->board,
            'status' => $this->status,
            'result' => $this->result,
            // 'rounds' => $this->rounds,
        ]);
        $array= json_decode($res,true);
        return json_encode($array);;
    }
}
