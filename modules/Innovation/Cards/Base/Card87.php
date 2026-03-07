<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card87 extends AbstractCard
{
  // Composites:
  //   - I DEMAND you transfer all but one card from your hand to my hand! Also transfer the
  //     highest card from your score pile to my score pile!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      $numCards = self::countCards(Locations::HAND) - 1;
      return self::youMust()->exactly($numCards)->fromYourHand()->toMine();
    } else {
      return self::youMust()->highest()->fromYourScore()->toMine();
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::countCards(Locations::HAND) > 1 || self::countCards(Locations::SCORE) > 0;
  }

}