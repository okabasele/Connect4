<?php

namespace ConnectFour;

use App\Entity\Game;
use App\Entity\Player;

class Board
{
    const COLUMNS = 7;
    const ROWS = 6;

    private $cells;

    public function __construct()
    {
        $this->initializeCells();
    }

    private function initializeCells()
    {
        for ($i = 0; $i < self::COLUMNS; ++$i) {
            $this->cells[$i] = array();
            for ($j = 0; $j < self::ROWS; ++$j) {
                $this->cells[$i][$j] = null;
            }
        }
    }

    public function reset()
    {
        $this->initializeCells();
    }

    public function getCells() : array
    {
        return $this->cells;
    }

    public function getDisc(int $column, int $row, Player $player = null) // : Disc - cannot return null, waiting for php 7.1
    {
        if (!$this->isInBounds($column, self::COLUMNS) || !$this->isInBounds($row, self::ROWS)) {
            throw new OutOfBoardException();
        }
        $disc = $this->cells[$column][$row];

        if (
            ($player && $disc && $disc->getPlayer() != $player) ||
            !$disc
        ) {
            throw new DiscNotFoundException();
        }

        return $disc;
    }

    public function countDiscs() : int
    {
        $count = 0;
        foreach ($this->cells as $row) {
            // array_filter purges null values before counting
            $count += count(array_filter($row));
        }

        return $count;
    }

    private function isInBounds($index, $maxBound) : bool
    {
        return $index >= 0 && $index < $maxBound;
    }

    public function addDisc($column, $player) : int
    {
        if (!$this->isInBounds($column, self::COLUMNS)) {
            throw new OutOfBoardException();
        }

        $higherFreeRow = $this->getHigherFreeRow($column);
        $disc = new Disc($player);
        $this->cells[$column][$higherFreeRow] = $disc;

        return $higherFreeRow;
    }

    public function isFull() : bool
    {
        return $this->countDiscs() === self::COLUMNS * self::ROWS;
    }

    private function getHigherFreeRow($column) : int
    {
        $row = 0;

        while ($row < self::ROWS) {
            if (!$this->cells[$column][$row]) {
                return $row;
            }
            ++$row;
        }
        throw new OutOfBoardException();
    }

    public function getCellsToString(Game $game) : string
    {
        $result="";
        for ($i = 0; $i < self::COLUMNS; ++$i) {
            $tmp = "";
            for ($j = 0; $j < self::ROWS; ++$j) {
                if ($this->cells[$i][$j] == null) {
                    $tmp.= ".";
                } else {
                    $disc = $this->cells[$i][$j];
                    $player = $disc->getPlayer();
                    if ($game->getPlayerOne()->getId()==$player->getId()) {
                        $tmp.= 'y';
                    } else {
                        $tmp.= 'r';

                    }
                }
                
            }
            if ($i!=self::COLUMNS) {
                $result .= "[{$tmp}]\n";
            } else {
                $result .= "[{$tmp}]";

            }
        }
        return $result;
    }
}