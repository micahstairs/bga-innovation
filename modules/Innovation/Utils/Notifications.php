<?php

namespace Innovation\Utils;

/* Class used to handle notifications */
class Notifications
{

  protected \Table $game;

  function __construct(\Table $game)
  {
    $this->game = $game;
  }

  public function notifyPresenceOfIcon(int $icon)
  {
    $this->game->notifyGeneralInfo(clienttranslate('It has a ${icon}.'), ['icon' => $this->getIconSquare($icon)]);
  }

  public function notifyAbsenceOfIcon(int $icon)
  {
    $this->game->notifyGeneralInfo(clienttranslate('It does not have a ${icon}.'), ['icon' => $this->getIconSquare($icon)]);
  }

  public function motifyIconChoice(int $icon, int $playerId)
  {
    $iconSquare = $this->getIconSquare($icon);
    $this->game->notifyPlayer($playerId, 'log', clienttranslate('${You} choose ${icon}.'), ['You' => 'You', 'icon' => $iconSquare]);
    $this->game->notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} chooses ${icon}.'), ['player_name' => $this->game->getColoredPlayerName($playerId), 'icon' => $iconSquare]);
  }

  private function getIconSquare(int $icon): string
  {
    return $this->game->getIconSquare($icon);
  }

}