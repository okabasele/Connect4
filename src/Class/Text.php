<?php

class Text
{
    const STATUS = [
        "GAME_OVER" => "Vous avez perdu la partie",
        "GAME_WIN" => "Vous avez gagné la partie",
        "WINNER" => "Le gagnant est : %s",
        "VICTORY" => "Vous avez eu %d victoire",
        "DRAW" => "Égalité"

    ];

    const GAMEPLAY = [
        "PLAYING" => "Votre tour",
        "WAITING" => "Réflechit..."
    ];

    const BUTTON = [
        "START" => "COMMENCER",
        "CANCEL" => "ANNULER",
        "CONFIRM" => "CONFIRMER",
        "CONTINUE" => "CONTINUER",
        "REMATCH" => "REJOUER",
        "STOP" => "ARRÊTER",
        "ONE_PLAYER" => "1 JOUEUR",
        "TWO_PLAYER" => "2 JOUEURS"
    ];

    const FORM = [
        "LABEL"=>"Nom d'utilisateur",
        "PLACEHOLDER"=>"Entrez votre nom d'utilisateur"
    ];

    const AI = "ordinateur";
}
