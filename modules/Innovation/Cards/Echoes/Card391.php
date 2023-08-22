<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card391 extends Card
{

  // Dentures
  // - 3rd edition
  //   - ECHO: Draw and tuck a [6].
  //   - Score the top two non-bottom cards of the color of the last card you tucked due to
  //     Dentures. If there are none to score, draw and tuck a [6], then repeat this dogma effect.
  // - 4th edition
  //   - ECHO: Draw and tuck a [6].
  //   - Score the top two non-bottom cards of the color of the last card you tucked due to
  //     Dentures. If there are none to score, draw and tuck a [6], then repeat this effect.
  //   - You may splay your blue cards right.

  public function initialExecution()
  {
    if (self::isEcho()) {
      $card = self::drawAndTuck(6);
      $this->game->setIndexedAuxiliaryValue(self::getPlayerId(), $card['color']); // Track last color tucked
    } else if (self::isFirstNonDemand()) {
      if (!$this->game->echoEffectWasExecuted()) {
        return;
      }
      $color = $this->game->getIndexedAuxiliaryValue(self::getPlayerId());
      do {
        $continue = false;
        $count = self::countCardsKeyedByColor('board')[$color];
        if ($count > 2) {
          self::score(self::getTopCardOfColor($color));
          self::score(self::getTopCardOfColor($color));
        } else if ($count === 2) {
          self::score(self::getTopCardOfColor($color));
        } else if ($count === 1) {
          $card = self::drawAndTuck(6);
          $color = $card['color'];
          $continue = true;
        }
      } while ($continue);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => $this->game::RIGHT,
      'color'           => [$this->game::BLUE],
    ];
  }

}