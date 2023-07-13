<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card507 extends Card
{

  // Knights Templar
  //   - I DEMAND you unsplay a splayed color on your board! If you do, transfer the top card on
  //     your board of that color to my score pile!
  //   - You may splay your red or green cards left.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'splay_direction'     => $this->game::UNSPLAYED,
        'has_splay_direction' => [$this->game::LEFT, $this->game::RIGHT, $this->game::UP, $this->game::ASLANT],
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::LEFT,
        'color'           => [$this->game::RED, $this->game::GREEN],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
        if (self::getNumChosen() > 0) {
          $card = self::getTopCardOfColor(self::getLastSelectedColor());
          self::transferToScorePile($card, self::getLauncherId());
        }
    }
  }
}