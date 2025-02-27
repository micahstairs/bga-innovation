<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card446 extends AbstractCard
{

  // Near-Field Comm:
  //   - I DEMAND you transfer all the cards of the value of my choice from your score pile to my score pile!
  //   - Reveal and self-execute the highest card in your score pile.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->chooseValue()->ofMyChoice()->build();
    } else {
      return self::youMust()->reveal()->highest()->fromYourScore()->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::transferToScorePile($card, self::getLauncherId());
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::transferToScorePile($card);
      self::selfExecute($card);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}