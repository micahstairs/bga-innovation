<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Directions;

class Card536 extends AbstractCard
{

  // Reconnaissance:
  //   - I DEMAND you reveal your hand!
  //   - Draw and reveal three [7]. Return two of the drawn cards.  You may splay the color of the
  //     card not returned right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::revealHand();
    } else {
      $cardIds = array();
      for ($i = 0; $i < 3; $i++) {
        $card = self::drawAndReveal(7);
        $cardIds[] = self::getId($card);
      }
      self::setAuxiliaryArray($cardIds);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->exactly(2)->fromYourRevealed()->onlyCardsInAuxiliaryArray()->build();
    } else {
      return self::youMay()->splayRight(self::getAuxiliaryValue())->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $revealedCard = self::getRevealedCard();
      self::transferToHand($revealedCard);
      self::setAuxiliaryValue($revealedCard['color']);
    }
  }

}