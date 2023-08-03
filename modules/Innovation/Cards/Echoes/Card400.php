<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card400 extends Card
{

  // Telegraph
  // - 3rd edition
  //   - You may choose an opponent and a color. Match your splay in that color to theirs.
  //   - You may splay your blue cards up.
  // - 4th edition
  //   - You may choose another player and a color, and match your splay in that color to theirs.
  //   - You may splay your blue cards up.
  //   - If Telegraph was foreseen, splay all your splayed colors up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::wasForeseen()) {
      for ($color = 0; $color < 5; $color++) {
        if (self::isSplayed($color)) {
          self::splayUp($color);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::getCurrentStep() === 1) {
        return [
          'can_pass'      => true,
          'choose_player' => true,
          'players'       => $this->game->getOtherActivePlayers(self::getPlayerId()),
        ];
      } else {
        return [
          'can_pass'     => true,
          'choose_color' => true,
        ];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::BLUE],
      ];
    }
  }


  public function handleSpecialChoice(int $choice)
  {
    if (self::getCurrentStep() === 1) {
      self::setAuxiliaryValue($choice); // Track chosen player
      self::setMaxSteps(2);
    } else {
      $player = self::getAuxiliaryValue();
      $color = $choice;
      $direction = self::getSplayDirection($color, $player);
      $this->game->splay(self::getPlayerId(), self::getPlayerId(), $color, $direction, /*force_unsplay=*/$direction === 0);
    }
  }

}