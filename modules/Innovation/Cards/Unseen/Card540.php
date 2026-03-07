<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card540 extends AbstractCard
{

  // Swiss Bank Account:
  //   - Safeguard an available achievement of value equal to the number of cards in your score
  //     pile. If you do, score all cards in your hand of its value.
  //   - Draw a [6] for each secret in your safe.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      $numCardsInSafe = self::countCards('safe');
      for ($i = 0; $i < $numCardsInSafe; $i++) {
        self::draw(6);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $value = self::countCards(Locations::SCORE);
    return self::youMust()->safeguard()->value($value);
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      $cards = self::getCardsKeyedByValue(Locations::HAND)[self::getLastSelectedAge()];
      foreach ($cards as $card) {
        self::score($card);
      }
    }
  }

}