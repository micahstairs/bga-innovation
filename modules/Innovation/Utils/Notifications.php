<?php

namespace Innovation\Utils;

/* Class used to handle notifications */
class Notifications
{

  protected \Innovation $game;

  function __construct(\Innovation $game)
  {
    $this->game = $game;
  }

  public function notifyPresenceOfIcon(int $icon)
  {
    $this->game->notifyGeneralInfo(clienttranslate('It has a ${icon}.'), array('icon' => $this->game->getIconSquare($icon)));
  }

  public function notifyAbsenceOfIcon(int $icon)
  {
    $this->game->notifyGeneralInfo(clienttranslate('It does not have a ${icon}.'), array('icon' => $this->game->getIconSquare($icon)));
  }

}