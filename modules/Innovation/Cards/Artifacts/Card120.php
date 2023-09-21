<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card120 extends AbstractCard
{

  // Lurgan Canoe
  // - 3rd edition:
  //   - Meld a card from your hand. Score all other cards of the same color from your board. If
  //     you scored at least one card, repeat this effect.
  // - 4th edition:
  //   - Meld a card from your hand. Score all other cards of the same color from your board. If
  //     you score a card, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'hand',
      'meld_keyword'  => true,
    ];
  }

  public function handleCardChoice(array $meldedCard) {
    foreach (array_reverse(self::getStack($meldedCard['color'])) as $card) {
      if ($card['id'] != $meldedCard['id']) {
        self::score($card);
        self::setNextStep(1);
      }
    }
  }

}