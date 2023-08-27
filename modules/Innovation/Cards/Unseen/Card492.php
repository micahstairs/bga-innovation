<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card492 extends Card
{

  // Myth:
  //   - If you have two cards of the same color in your hand, tuck them both. If you do, splay
  //     left that color, and draw and safeguard a card of value equal to the value of your bottom
  //     card of that color.

  public function initialExecution()
  {
    $cardIds = [];
    $counts = self::countCardsKeyedByColor('hand');
    foreach (self::getCards('hand') as $card) {
        if ($counts[$card['color']] >= 2) {
            $cardIds[] = $card['id'];
        }
    }
    if (count($cardIds) >= 2) {
        self::setMaxSteps(2);
        self::setAuxiliaryArray($cardIds);
    } else if (self::countCards('hand') >= 2) {
        // Reveal that no matching colors exist
        self::revealHand();
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'tuck_keyword' => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'tuck_keyword' => true,
        'color' => [self::getLastSelectedColor()],
      ];
    }
  }

  public function afterInteraction() {
    if (self::isSecondInteraction()) {
      $color = self::getLastSelectedColor();
      $bottomCard = self::getBottomCardOfColor($color);
      $valueToDraw = 0;
      if ($bottomCard) {
          self::splayLeft($color);
          $valueToDraw = $bottomCard['age'];
      }
      self::drawAndSafeguard($valueToDraw);
    }
  }
  
}