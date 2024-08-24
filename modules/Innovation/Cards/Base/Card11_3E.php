<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card11_3E extends AbstractCard
{
  // Masonry (3rd edition):
  //   - You may meld any number of cards from your hand, each with a [AUTHORITY]. If you melded
  //     four or more cards in this way, claim the Monument achievement.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->meld()->anyNumber()->fromHand()->withIcon(Icons::AUTHORITY)->build();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() >= 4) {
      self::claim(CardIds::MONUMENT);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::getCards(Locations::HAND), Icons::AUTHORITY)) > 0;
  }

}