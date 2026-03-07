<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card168 extends AbstractCard
{
  // U.S. Declaration of Independence
  //   - I COMPEL you to transfer the highest card in your hand to my hand, the highest card in
  //     your score pile to my score pile, and the highest top card with [INDUSTRY] from your
  //     board to my board!

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      $value = self::getMaxValueInLocation(Locations::HAND);
      return self::youMust()->value($value)->fromYourHand()->toMine();
    } else if (self::isSecondInteraction()) {
      $value = self::getMaxValueInLocation(Locations::SCORE);
      return self::youMust()->value($value)->fromYourScore()->toMine();
    } else {
      $value = $this->game->getMaxAgeOnBoardTopCardsWithIcon(self::getPlayerId(), Icons::INDUSTRY);
      return self::youMust()->value($value)->fromYourBoard()->toMine()->withIcon(Icons::INDUSTRY);
    }
  }

  public function compelMightBeEffective(): bool
  {
    if (count(self::filterByIcon(self::getTopCards(), Icons::INDUSTRY)) > 0) {
      return true;
    }
    return self::hasCards(Locations::HAND) || self::hasCards(Locations::SCORE);
  }

}