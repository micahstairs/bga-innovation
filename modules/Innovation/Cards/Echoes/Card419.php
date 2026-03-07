<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card419 extends AbstractCard
{

  // Credit Card
  // - 3rd edition
  //   - ECHO: Draw and foreshadow a [9].
  //   - You may take a top non-green card from your board into your hand. If you do, draw and
  //     score a card of equal value.
  //   - You may splay your green cards up.
  // - 4th edition
  //   - ECHO: Draw and foreshadow a [9].
  //   - You may transfer a top non-green card from your board to your hand. If you do, draw and
  //     score a card of equal value. If you do, and Credit Card was foreseen, repeat this effect.
  //   - You may splay your green cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(9);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->non(Colors::GREEN)->fromYourBoard()->toYourHand();
    } else {
      return self::youMay()->splayUp(Colors::GREEN);
    }
  }

  public function handleCardChoice(array $card)
  {
    $scoredCard = self::drawAndScore(self::getFaceupValue($card));
    if (self::getValue($scoredCard) == self::getFaceupValue($card) && self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

}