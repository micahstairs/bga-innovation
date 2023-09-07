<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card130 extends Card
{

  // Baghdad Battery
  // - 3rd edition:
  //   - Meld a card from hand. If you covered up a card of different type than the melded card,
  //     draw a card of matching type and value to the covered card, then score a card from your
  //     hand.
  // - 4th edition:
  //   - Meld a card from your hand. Score a card from your hand. If you do both, and the cards
  //     have different values, junk all cards in the decks of both values.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      if (self::isFirstOrThirdEdition()) {
        $stack = self::getCardsKeyedByColor('board')[$card['color']];
        if (count($stack) >= 2) {
          $coveredCard = $stack[count($stack) - 2];
          if ($coveredCard['type'] != $card['type']) {
            self::drawType($coveredCard['faceup_age'], $coveredCard['type']);
            self::setMaxSteps(2);
          }
        }
      } else {
        self::setAuxiliaryValue($card['faceup_age']); // Track value of melded card
        self::setMaxSteps(2);
      }
    } else if (self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::junkBaseDeck(self::getAuxiliaryValue());
      self::junkBaseDeck($card['age']);
    }
  }

}