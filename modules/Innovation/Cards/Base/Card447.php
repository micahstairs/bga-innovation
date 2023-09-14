<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card447 extends Card
{

  // Reclamation:
  //   - Return your three bottom red cards. Draw and meld a card of value equal to half the total
  //     sum value of the returned cards, rounded up. If you returned three cards, repeat this
  //     effect using the color of the melded card.

  public function initialExecution()
  {
    self::setAuxiliaryValue(Colors::RED); // Track the color of the cards being returned
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $color = self::getAuxiliaryValue();
    $stack = self::getCardsKeyedByColor(Locations::BOARD)[$color];

    $cardIds = [];
    for ($i = 0; $i < min(3, count($stack)); $i++) {
      $cardIds[] = $stack[$i]['id'];
    }
    self::setAuxiliaryArray($cardIds);

    self::setAuxiliaryValue2(0); // Track the sum of the values of the cards being returned

    return [
      'n' => count($cardIds),
      'location_from' => Locations::PILE,
      'color' => $color,
      'return_keyword' => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleAbortedInteraction() {
    // If no cards are returned, we still need to draw and meld a card
    self::drawAndMeld(0);
  }

  public function handleCardChoice(array $card) {
    self::incrementAuxiliaryValue($card['faceup_age']);
  }

  public function afterInteraction() {
    $card = self::drawAndMeld(ceil(self::getAuxiliaryValue2() / 2));
    if (self::getNumChosen() === 3) {
      self::setNextStep(1);
    }
  }

}