<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card550 extends AbstractCard
{

  // Plot Voucher
  //   - Meld a card from your score pile. Safeguard the lowest available standard achievement. 
  //     If you do, super-execute the melded card if it is your turn, or if it is not your turn
  //     self-execute it.

  public function initialExecution()
  {
    self::setAuxiliaryValue(-1);
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->fromYourScore();
    } else {
      return self::youMust()->safeguard()->value(self::getMinValueInLocation(Locations::AVAILABLE_ACHIEVEMENTS));
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(self::getId($card));
    } else {
      // Make sure the card is actually in the safe (the safe could have been full)
      if (self::getLocation($card) == 'safe' && self::getOwner($card) == self::getPlayerId()) {
        $meldedCard = self::getCard(self::getAuxiliaryValue());
        if (self::getPlayerId() === self::getLauncherId()) {
          self::superExecute($meldedCard);
        } else {
          self::selfExecute($meldedCard);
        }
      }
    }
  }

}