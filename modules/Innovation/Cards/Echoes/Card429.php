<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card429 extends AbstractCard
{

  // GPS
  // - 3rd edition 
  //   - I DEMAND you return all cards from your forecast!
  //   - Draw and foreshadow three [10].
  //   - You may splay your yellow cards up.
  // - 4th edition
  //   - I DEMAND you return all cards from your forecast!
  //   - You may splay your yellow cards up.
  //   - Draw three [11]. If GPS was foreseen, foreshadow them.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow(10);
      self::drawAndForeshadow(10);
      self::drawAndForeshadow(10);
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      $card1 = self::draw(11);
      $card2 = self::draw(11);
      $card3 = self::draw(11);
      if (self::wasForeseen()) {
        self::setAuxiliaryArray([self::getId($card1), self::getId($card2), self::getId($card3)]); // Track cards to foreshadow
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->return()->all()->fromYourForecast();
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      return self::youMust()->foreshadow()->exactly(3)->onlyCardsInAuxiliaryArray()->fromYourHand();
    } else {
      return self::youMay()->splayUp(Colors::YELLOW);
    }
  }

}