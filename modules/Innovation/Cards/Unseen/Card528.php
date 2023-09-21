<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card528 extends AbstractCard
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
    if (self::isFirstInteraction()) {
      $card = self::transferToHand(self::drawAndReveal(5));
      $returnedCard = self::return(self::getTopCardOfColor($card['color']));
      self::setAuxiliaryValue($returnedCard ? 1 : 0); // Track how many cards were returned
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
        'not_id'         => $card['id'],
        'color'          => [$card['color']],
      ];
    } else {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() === 0) {
      self::revealHand();
    } else if (self::isSecondInteraction()) {
      if (self::getNumChosen() === 0) {
        self::revealScorePile();
      }
      if (self::getAuxiliaryValue() === 3) {
        self::setNextStep(1);
      }
    }
  }

}