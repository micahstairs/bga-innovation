<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card93_4E extends AbstractCard
{
  // Services (4th edition):
  //   - I DEMAND you transfer all the cards of the value of my choice from your score pile to my
  //     hand! If you do, transfer a top card without [HEALTH] from my board to your hand!

  public function initialExecution()
  {
    if (self::countCards(Locations::SCORE)) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue()->ofMyChoice();
    } else {
      return self::youMust()->withoutIcon(Icons::HEALTH)->fromMyBoard()->toYourHand();
    }
  }

  public function handleValueChoice(int $value)
  {
    $numCardsTransferred = 0;
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::transferToHand($card, self::getLauncherId());
      $numCardsTransferred++;
    }
    if ($numCardsTransferred > 0) {
      self::setMaxSteps(1);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}