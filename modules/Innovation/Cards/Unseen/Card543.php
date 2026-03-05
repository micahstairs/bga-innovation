<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card543 extends AbstractCard
{

  // Illuminati:
  //   - Reveal a card in your hand. Splay the card's color on your board right. Safeguard the top
  //     card on your board of that color. Safeguard an available achievement of value one higher
  //     than the secret.

  public function initialExecution()
  {
    if (self::countCards(Locations::HAND) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->revealAndPlaceInHand()->fromYourHand();
    } else {
      return self::youMust()->safeguard()->value(self::getAuxiliaryValue());
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && self::isFirstInteraction()) {
      $color = self::getLastSelectedColor();
      self::splayRight($color);
      $topCard = self::getTopCardOfColor($color);
      if ($topCard) {
        self::safeguard($topCard);
        self::setMaxSteps(2);
        self::setAuxiliaryValue(self::getValue($topCard) + 1);
      }
    }
  }

}