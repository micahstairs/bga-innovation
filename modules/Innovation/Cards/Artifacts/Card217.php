<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card217 extends AbstractCard
{

  // Newton-Wickins Telescope
  //   - You may return any number of cards from your score pile. If you do, draw and meld a card
  //     of value equal to the number of cards returned. If the melded card has a [EFFICIENCY], return it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->return()->anyNumber()->fromYourScore();
  }

  public function afterInteraction()
  {
    $numCardsReturned = self::getNumChosen();
    if ($numCardsReturned > 0) {
      $card = self::drawAndMeld($numCardsReturned);
      if (self::hasIcon($card, Icons::EFFICIENCY)) {
        self::return($card);
      }
    }
  }

}