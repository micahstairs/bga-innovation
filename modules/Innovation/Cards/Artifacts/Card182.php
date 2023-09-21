<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card182 extends AbstractCard
{
  // Singer Model 27
  // - 3rd edition:
  //   - Tuck a card from your hand. If you do, splay up its color, and then tuck all cards from
  //     your score pile of that color.
  // - 4th edition:
  //   - Tuck a card from your hand. If you do, splay up its color, and then tuck all cards from
  //     your score pile of that color. If you do, junk an available standard achievement.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'tuck_keyword' => true,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'n' => 'all',
        'location_from' => Locations::SCORE,
        'tuck_keyword' => true,
        'color' => [self::getLastSelectedColor()],
        'reveal_if_unable' => true,
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstInteraction()) {
      self::splayUp($card['color']);
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction() {
    if (self::isFourthEdition() && self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::setMaxSteps(3);
    }
  }

}