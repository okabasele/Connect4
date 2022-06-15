<?php

namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Repository\WaitingListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WaitingListRepository::class)]
#[ApiResource(
    [
        'normalizationContext' => ['groups' => 'waitinglist:read'],
        'denormalizationContext' => ['groups' => 'waitinglist:write']
    ],
    paginationEnabled: false,
)]
class WaitingList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['waitinglist:read'])]
    private $id;

    // #[ORM\Column(type: 'string', length: 255)]
    #[ORM\OneToOne(targetEntity: Player::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "player_id", referencedColumnName: "id")]
    #[Groups(['waitinglist:read','waitinglist:write'])]
    private $player;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['waitinglist:read'])]
    private $enteredAt;

    public function __construct() {
        $this->enteredAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getEnteredAt(): ?\DateTimeInterface
    {
        return $this->enteredAt;
    }

    public function setEnteredAt(\DateTimeInterface $enteredAt): self
    {
        $this->enteredAt = $enteredAt;

        return $this;
    }

    public function __toString()
    {
        // return "id :".$this->id." / date : ".$this->enteredAt->format("d-m-Y H:i:s")."/ ".$this->player;
        $res = json_encode([
            'id' => $this->id,
            'username' => $this->username,
            'enteredAt'=>$this->enteredAt
        ]);
        $array= json_decode($res,true);
        return json_encode($array);;
    }

}
