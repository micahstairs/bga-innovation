<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card446 extends AbstractCard
{

  // Near-Field Comm:
  //   - I DEMAND you transfer all the highest cards in your score pile to my score pile!
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
        'n'             => 'all',
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::SCORE,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::SCORE,
        'age'           => $maxScoreValue,
      ];
    } else {
      return [
        'location_from' => Locations::SCORE,
        'location_to'   => Locations::REVEALED,
        'age'           => $maxScoreValue,
      ];
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