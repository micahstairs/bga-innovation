<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;

class Card87 extends AbstractCard
{
  // Composites:
  //   - I DEMAND you transfer all but one card from your hand to my hand! Also transfer the
  //     highest card from your score pile to my score pile!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'        => self::countCards(Locations::HAND) - 1,
        'location' => Locations::HAND,
        'owner_to' => self::getLauncherId(),
      ];
    } else {
      return [
        'age'      => ValueSelectors::HIGHEST,
        'location' => Locations::SCORE,
        'owner_to' => self::getLauncherId(),
      ];
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::countCards(Locations::HAND) > 1 || self::countCards(Locations::SCORE) > 0;
  }

}