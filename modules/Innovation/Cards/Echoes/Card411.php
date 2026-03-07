<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card411 extends AbstractCard
{

  // Air Conditioner
  // - 3rd edition
  //   - ECHO: You may score a card from your hand.
  //   - I DEMAND you return all cards from your score pile of value matching any of your top cards!
  // - 4th edition
  //   - ECHO: You may score a card from your hand.
  //   - I DEMAND you return all cards from your score pile of value matching any of your top cards!
  //   - Junk all cards in the [9] deck.

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::junkBaseDeck(9);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMay()->score()->fromYourHand();
    } else {
      $topCards = self::getTopCards();
      $cardIds = [];
      foreach (self::getCards(Locations::SCORE) as $scorePileCard) {
        $found = false;
        foreach ($topCards as $topCard) {
          if (self::getFaceupValue($topCard) == self::getValue($scorePileCard)) {
            $found = true;
            break;
          }
        }
        if ($found) {
          $cardIds[] = self::getId($scorePileCard);
        }
      }
      self::setAuxiliaryArray($cardIds);
      $numCards = count(self::getAuxiliaryArray());
      return self::youMust()->return()->exactly($numCards)->onlyCardsInAuxiliaryArray()->fromYourScore();
    }
  }

}