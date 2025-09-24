<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card400 extends AbstractCard
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
      foreach (Colors::ALL as $color) {
        if (self::isSplayed($color)) {
          self::splayUp($color);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMay()->choosePlayer(self::getOtherPlayers())->build();
      } else {
        return self::youMay()->chooseColor()->build();
      }
    } else {
      return self::youMay()->splayUp(Colors::BLUE)->build();
    }
  }


  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Track chosen player
    self::setMaxSteps(2);
  }

  public function handleColorChoice(int $color)
  {
    $playerId = self::getAuxiliaryValue();
    $direction = self::getSplayDirection($color, $playerId);
    $this->game->splay(self::getPlayerId(), self::getPlayerId(), $color, $direction, /*force_unsplay=*/ $direction === 0);
  }

}