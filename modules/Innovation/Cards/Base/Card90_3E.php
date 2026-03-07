<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card90_3E extends AbstractCard
{
  // Satellites (3rd edition):
  //   - Return all cards from your hand, and draw three [8].
  //   - You may splay your purple cards up.
  //   - Meld a card from your hand and then execute each of its non-demand dogma effects. Do not share them.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand();
    } else if (self::isSecondNonDemand()) {
      return self::youMay()->splayUp(Colors::PURPLE);
    } else {
      return self::youMust()->meld()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdNonDemand()) {
      self::selfExecute($card);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      self::draw(8);
      self::draw(8);
      self::draw(8);
    }
  }

  public function handleAbortedInteraction()
  {
    if (self::isFirstNonDemand()) {
      self::draw(8);
      self::draw(8);
      self::draw(8);
    }
  }

}