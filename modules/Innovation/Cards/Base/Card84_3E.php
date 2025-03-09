<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card84_3E extends AbstractCard
{
  // Socialism (3rd edition):
  //   - You may tuck all cards from your hand. If you tuck one, you must tuck them all. If you
  //     tucked at least one purple card, take all the lowest cards in each other player's hand
  //     into your hand.

  public function initialExecution()
  {
    self::setAuxiliaryValue(0); // Track how many purple cards have been tucked
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->tuck()->all()->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    if (self::isPurple($card)) {
      self::incrementAuxiliaryValue();
    }
  }

  public function afterInteraction()
  {
    if (self::getAuxiliaryValue() > 0) {
      self::notifyAll(clienttranslate('At least one purple card has been tucked.'));
      foreach (self::getOpponentIds() as $opponentId) {
        foreach (self::getLowestCards(Locations::HAND, $opponentId) as $card) {
          self::transferToHand($card);
        }
      }
    } else {
      self::notifyAll(clienttranslate('No purple card has been tucked.'));
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}