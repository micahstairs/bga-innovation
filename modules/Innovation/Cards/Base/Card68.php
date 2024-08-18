<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card68 extends AbstractCard
{
  // Explosives:
  // - 3rd edition:
  //   - I DEMAND you transfer the three highest cards from your hand to my hand! If you
  //     transferred any, and then have no card in hand, draw a [7]!
  // - 4th edition:
  //   - I DEMAND you transfer the three highest cards from your hand to my hand! If you
  //     transfer any, and have no cards in hand, draw a [7]!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'                 => 3,
      'location'          => Locations::HAND,
      'owner_from'        => self::getPlayerId(),
      'owner_to'          => self::getLauncherId(),
      'age'               => 'highest',
      'refresh_selection' => true,
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && !self::hasCards(Locations::HAND)) {
      self::draw(7);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}