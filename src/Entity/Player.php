<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ApiResource(
    [
        'normalizationContext' => ['groups' => 'player:read'],
        'denormalizationContext' => ['groups' => 'player:write']
    ],
    paginationEnabled: false,
    mercure: true
)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['player:read', 'round:read', 'round:write', 'game:read', 'game:write'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['player:read', 'player:write', 'round:read', 'round:write', 'game:read', 'game:write'])]
    private $username;

    private $color;

    #[ORM\OneToMany(mappedBy: 'player_one', targetEntity: Game::class)]
    private $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
    }

    public function dropDisc(Game $game, int $column, bool $addMove = true)
    {
        $game->addDisc($column, $this, $addMove);
    }

    public function __toString()
    {
        // return sprintf('Player(%s,%s)', $this->username,$this->color);
        $res = json_encode([
            'id' => $this->id,
            'username' => $this->username,
        ]);
        $array= json_decode($res,true);
        return json_encode($array);
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    // public function addGame(Game $game): self
    // {
    //     if (!$this->games->contains($game)) {
    //         $this->games[] = $game;
    //         $game->setPlayerOne($this);
    //     }

    //     return $this;
    // }

    // public function removeGame(Game $game): self
    // {
    //     if ($this->games->removeElement($game)) {
    //         // set the owning side to null (unless already changed)
    //         if ($game->getPlayerOne() === $this) {
    //             $game->setPlayerOne(null);
    //         }
    //     }
    //     return $this;
    // }
}
