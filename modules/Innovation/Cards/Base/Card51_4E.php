<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card51_4E extends AbstractCard
{
  // Statistics (4th edition):
  //   - I DEMAND you transfer all the cards of the value of my choice in your score pile to your hand!
  //   - You may splay your yellow cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'player_id' => self::getLauncherId(),
        'choose_value' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::YELLOW],
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::transferToHand($card);
    }
  }

}