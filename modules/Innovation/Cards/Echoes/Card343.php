<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Directions;

class Card343 extends AbstractCard
{

  // Flute
  // - 3rd edition:
  //   - ECHO: You may splay one color of your cards left.
  //   - I DEMAND you return a card with a bonus from your hand!
  //   - Draw and reveal a [1]. If it has a bonus, draw a [1].
  // - 4th edition:
  //   - ECHO: You may splay one color of your cards left.
  //   - I DEMAND you return an expansion card from your hand!
  //   - Draw and reveal an Echoes [1]. If it has a bonus, draw a [1].

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        $card = self::drawAndReveal(1);
      } else {
        $card = self::drawAndRevealType(1, CardTypes::ECHOES);
      }
      self::transferToHand($card);
      if (self::hasBonusIcon($card)) {
        self::draw(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMay()->splayLeft();
    } else if (self::isFirstOrThirdEdition()) {
      return self::youMust()->return()->withBonus()->fromYourHand()->revealingIfUnable();
    } else {
      $types = CardTypes::getAllTypesOtherThan(CardTypes::BASE);
      return self::youMust()->return()->withTypes($types)->fromYourHand();
    }
  }

}