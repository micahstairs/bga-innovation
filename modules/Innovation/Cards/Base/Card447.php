<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card447 extends AbstractCard
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

    $cardIds = [];
    foreach (self::getStack($color) as $card) {
      if ($card['position'] < 3) {
        $cardIds[] = $card['id'];
      }
    }
    self::setAuxiliaryArray($cardIds);

    self::setAuxiliaryValue2(0); // Track the sum of the values of the cards being returned

    return [
      'n'                               => count($cardIds),
      'location_from'                   => Locations::PILE,
      'color'                           => [$color],
      'return_keyword'                  => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleAbortedInteraction()
  {
    // If no cards are returned, we still need to draw and meld a card
    self::drawAndMeld(0);
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue2($card['faceup_age']);
  }

  public function afterInteraction()
  {
    $card = self::drawAndMeld(ceil(self::getAuxiliaryValue2() / 2));
    if (self::getNumChosen() === 3) {
      self::setNextStep(1);
    }
  }

}