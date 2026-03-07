<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card67_3E extends AbstractCard
{
  // Combustion (3rd edition):
  //   - I DEMAND you transfer one card from your score pile to my score pile for every four [PROSPERITY]
  //     on my board!
  //   - Return your bottom red card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::return(self::getBottomCardOfColor(Colors::RED));
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::PROSPERITY), 4);
    return self::youMust()->exactly($numCards)->fromYourScore()->toMine();
  }

  public function demandMightBeEffective(): bool
  {
    return self::getStandardIconCount(Icons::PROSPERITY, self::getLauncherId()) >= 4 && self::hasCards(Locations::SCORE);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::getCardsKeyedByColor(Locations::BOARD)[Colors::RED]) > 0;
  }

}