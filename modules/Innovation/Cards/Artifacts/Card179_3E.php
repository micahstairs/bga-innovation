<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card179_3E extends AbstractCard
{
  // International Prototype Metre Bar (3rd edition):
  //   - Choose a value. Draw and meld a card of that value. Splay up the color of the melded card.
  //     If the number of cards of that color visible on your board is exactly equal to the card's
  //     value, you win. Otherwise, return the melded card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return ['choose_value' => true];
  }

  public function handleValueChoice(int $value) {
    $this->notifications->notifyValueChoice($value, self::getPlayerId());
    $card = self::drawAndMeld($value);
    self::splayUp($card['color']);
    if ($card['faceup_age'] == self::countVisibleCardsInStack($card['color'])) {
      self::win();
    } else {
      self::return($card);
    }
  }

}