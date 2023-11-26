<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card446 extends AbstractCard
{

  // Near-Field Comm:
  //   - I DEMAND you transfer all the cards of the value of my choice from your score pile to my score pile!
  //   - Reveal and self-execute the highest card in your score pile.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $maxScoreValue = self::getMaxValueInLocation(Locations::SCORE);
    if (self::isDemand()) {
      return [
        'player_id' => self::getLauncherId(),
        'choose_value' => true,
      ];
    } else {
      return [
        'location_from' => Locations::SCORE,
        'location_to'   => Locations::REVEALED,
        'age'           => $maxScoreValue,
      ];
    }
  }

  public function handleValueChoice(int $value) {
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

}