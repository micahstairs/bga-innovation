<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card195 extends AbstractCard
{
  // Yeager's Bell X-1A
  // - 3rd edition:
  //   - Draw and meld a [9]. Execute the effects of the melded card as if they were on this card,
  //     without sharing. If that card has a [EFFICIENCY], repeat this effect.
  // - 4th edition:
  //   - Draw and meld a [9], and self-execute it. If that card has a [EFFICIENCY], repeat this effect.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::getPostExecutionIndex() > 0) {
      if (self::getAuxiliaryValue() === 1) {
        self::setPostExecutionIndex(0);
      } else {
        return [];
      }
    }

    $card = self::drawAndMeld(9);
    if (self::hasIcon($card, Icons::EFFICIENCY)) {
      self::setAuxiliaryValue(1); // Indicate that we should repeat the effect
    } else {
      self::setAuxiliaryValue(0); // Indicate that we should not repeat the effect
    }

    if (self::isFirstOrThirdEdition())  {
      self::fullyExecute($card);
    } else {
      self::selfExecute($card);
    }
  }

}