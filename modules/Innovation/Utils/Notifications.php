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

  public function notifyIconChoice(int $icon, int $playerId)
  {
    $iconSquare = $this->getIconSquare($icon);
    $this->game->notifyPlayer($playerId, 'log', clienttranslate('${You} choose ${icon}.'), ['You' => 'You', 'icon' => $iconSquare]);
    $this->game->notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} chooses ${icon}.'), ['player_name' => $this->game->getColoredPlayerName($playerId), 'icon' => $iconSquare]);
  }

  public function notifySafeIsFull(int $playerId)
  {
    $this->game->notifyPlayer($playerId, 'log', clienttranslate('${Your} safe was already full so the card was not transferred to your safe.'), ['Your' => 'Your']);
    $this->game->notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name}\'s safe was already full so the card was not transferred to his safe.'), ['player_name' => $this->game->getColoredPlayerName($playerId)]);
  }

  private function getIconSquare(int $icon): string
  {
    return $this->game->getIconSquare($icon);
  }

}