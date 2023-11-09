<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card130_3E extends AbstractCard
{

  // Baghdad Battery (3rd edition):
  //   - Meld a card from hand. If you covered up a card of different type than the melded card,
  //     draw a card of matching type and value to the covered card, then score a card from your
  //     hand.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $stack = self::getStack($card['color']);
      if (count($stack) >= 2) {
        $coveredCard = $stack[count($stack) - 2];
        if ($coveredCard['type'] != $card['type']) {
          self::drawType($coveredCard['faceup_age'], $coveredCard['type']);
          self::setMaxSteps(2);
        }
      }
    }
  }

}