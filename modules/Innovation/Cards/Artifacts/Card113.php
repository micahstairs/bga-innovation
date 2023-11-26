<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card113 extends AbstractCard
{

  // Holmegaard Bows
  // - 3rd edition:
  //   - I COMPEL you to transfer the highest top card with a [AUTHORITY] on your board to my hand!
  //   - Draw a [2].
  // - 4th edition:
  //   - I COMPEL you to transfer your highest top card with [AUTHORITY] to my hand! If you
  //     don't, junk all cards in the deck of value equal to the value of your lowest top card!
  //   - Draw a [2].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::draw(2);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::BOARD,
      'owner_to'      => self::getLauncherId(),
      'location_to'   => Locations::HAND,
      'with_icon'     => Icons::AUTHORITY,
      'age'           => $this->game->getMaxAgeOnBoardTopCardsWithIcon(self::getPlayerId(), Icons::AUTHORITY),
    ];
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isCompel() && self::getNumChosen() === 0) {
      self::junkBaseDeck(self::getMinValue(self::getTopCards()));
    }
  }

}