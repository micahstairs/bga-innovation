<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card418_4E extends AbstractCard
{

  // Jet (4th edition):
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the last color I melded due to Jet's echo effect during this
  //     action! Junk all available achievements of values equal to the melded card and the returned card!
  //   - Draw and foreshadow a [10].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      $cardIds = self::getActionScopedAuxiliaryArray(self::getLauncherId());
      if ($cardIds) {
        // NOTE: The array will contain at most one element.
        $meldedCard = self::getCard($cardIds[0]);
        $returnedCard = self::return(self::getTopCardOfColor(self::getColor($meldedCard)));
        $values = [self::getValue($meldedCard), self::getValue($returnedCard)];
        foreach (self::getAvailableStandardAchievements() as $card) {
          if (in_array(self::getValue($card), $values)) {
            self::junk($card);
          }
        }
      }
    } else if (self::isFirstNonDemand()) {
      self::drawAndForeshadow(10);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->fromYourHand();
  }

  public function handleCardChoice(array $card)
  {
    if (self::isEcho() && self::isLauncher()) {
      self::setActionScopedAuxiliaryArray([self::getId($card)], self::getPlayerId()); // Track melded card
    }
  }

}