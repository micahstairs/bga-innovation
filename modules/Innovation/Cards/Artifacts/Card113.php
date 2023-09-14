<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

class Card113 extends Card
{

  // Holmegaard Bows
  // - 3rd edition:
  //   - I COMPEL you to transfer the highest top card with a [AUTHORITY] on your board to my hand!
  //     If you don't, junk all cards in the deck of value equal to the value of the lowest top
  //     card on your board!
  //   - Draw a [2].
  // - 4th edition:
  //   - I COMPEL you to transfer the highest top card with a [AUTHORITY] on your board to my hand!
  //   - Draw a [2].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      self::draw(2);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'board',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'hand',
      'with_icon'     => Icons::AUTHORITY,
      'age'           => $this->game->getMaxAgeOnBoardTopCardsWithIcon(self::getPlayerId(), Icons::AUTHORITY),
    ];
  }

  public function afterInteraction()
  {
    if (self::isCompel() && self::getNumChosen() === 0) {
      self::junkBaseDeck(self::getMinValue(self::getTopCards()));
    }
  }

}