<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card25_4E extends AbstractCard
{
  // Alchemy (4th edition):
  //   - Draw and reveal a [4] for every color on your board with [AUTHORITY]. If any of the drawn
  //     cards are red, return all cards from your hand.
  //   - Meld a card from your hand, then score a card from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $numCardsToDraw = self::countColorsWithIcon(Icons::AUTHORITY);
      $drewRed = false;
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        $card = self::transferToHand(self::drawAndReveal(4));
        if (self::isRed($card)) {
          $drewRed = true;
        }
      }
      if ($drewRed) {
        self::notifyPlayer(clienttranslate('${You} drew a red card.'));
        self::notifyOthers(clienttranslate('${player_name} drew a red card.'));
        self::setMaxSteps(1);
      } else {
        self::notifyPlayer(clienttranslate('${You} did not draw a red card.'));
        self::notifyOthers(clienttranslate('${player_name} did not draw a red card.'));
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHandOrRevealed();
    } else if (self::isFirstInteraction()) {
      return self::youMust()->meld()->fromYourHand();
    } else {
      return self::youMust()->score()->fromYourHand();
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::getStandardIconCount(Icons::AUTHORITY) > 0 || self::hasCards(Locations::HAND);
  }

}