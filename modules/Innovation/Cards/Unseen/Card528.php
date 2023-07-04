<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card528 extends Card
{

  // Witch Trial:
  //   - I DEMAND you draw and reveal a [5]! Return your top card of the color of the drawn card,
  //     another card of that color from your hand, and a card from your score pile! If you do,
  //     repeat this effect!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      $card = self::putInHand(self::drawAndReveal(5));
      $returnedCard = self::return(self::getTopCardOfColor($card['color']));
      self::setAuxiliaryValue($returnedCard ? 1 : 0); // Track how many cards were returned
      return [
        'location_from' => 'hand',
        'location_to'   => 'deck',
        'not_id'        => $card['id'],
        'color'         => [$card['color']],
      ];
    } else {
      return [
        'location_from' => 'score',
        'location_to'   => 'deck',
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() === 2 && self::getAuxiliaryValue() === 3) {
      self::setNextStep(1);
    }
  }

}