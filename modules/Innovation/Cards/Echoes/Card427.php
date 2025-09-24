<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card427 extends AbstractCard
{

  // Camcorder
  // - 3rd edition 
  //   - I DEMAND you transfer all cards in your hand to my hand! Draw a [9]!
  //   - Meld all [9] from your hand. Return all other cards from your hand. Draw three [9].
  // - 4th edition
  //   - I DEMAND you transfer all cards in your hand to my hand! Draw a [9]!
  //   - Meld all [9] from your hand. If Camcorder wasn't foreseen, return all other cards from
  //     your hand, and draw three [9].

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach (self::getCards('hand') as $card) {
        self::transferToHand($card, self::getLauncherId());
      }
      self::draw(9);
    } else {
      if (self::wasForeseen()) {
        self::setMaxSteps(1);
      } else {
        self::setMaxSteps(2);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->all()->value(9)->fromYourHand()->build();
    } else {
      return self::youMust()->return()->all()->fromYourHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::draw(9);
      self::draw(9);
      self::draw(9);
    }
  }

}