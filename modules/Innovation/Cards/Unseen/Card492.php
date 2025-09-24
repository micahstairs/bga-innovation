<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card492 extends AbstractCard
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
      if ($counts[self::getColor($card)] >= 2) {
        $cardIds[] = self::getId($card);
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
      return self::youMust()->tuck()->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
    } else {
      return self::youMust()->tuck()->withColor(self::getLastSelectedColor())->fromYourHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $color = self::getLastSelectedColor();
      $bottomCard = self::getBottomCardOfColor($color);
      $valueToDraw = 0;
      if ($bottomCard) {
        self::splayLeft($color);
        $valueToDraw = self::getValue($bottomCard);
      }
      self::drawAndSafeguard($valueToDraw);
    }
  }

}