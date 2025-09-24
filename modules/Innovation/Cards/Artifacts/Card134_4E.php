<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card134_4E extends AbstractCard
{

  // Cyrus Cylinder (4th edition):
  //   - Splay left a color on any player's board.
  //   - Choose any top purple card other than Cyrus Cylinder on any player's board. Self-execute it. 

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->build();
    } else {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->withColor(Colors::PURPLE)->otherThan(CardIds::CYRUS_CYLINDER)->build();
    }

  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::splayLeft(self::getColor($card), self::getOwner($card), self::getPlayerId());
    } else {
      self::selfExecute($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    // There are situations where this is not effective, but it's a complicated check and the vast
    // majority of the time it will be effective.
    return true;
  }

}