<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card540 extends Card
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

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'        => 0,
      'location_from'     => 'achievements',
      'safeguard_keyword' => true,
      'age'               => self::countCards('score'),
    ];

  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      $cards = self::getCardsKeyedByValue('hand')[self::getLastSelectedAge()];
      foreach ($cards as $card) {
        self::score($card);
      }
    }
  }

}