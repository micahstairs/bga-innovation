<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card476 extends AbstractCard
{

  // Digital Pet
  //   - I DEMAND you draw and reveal an [11]! Return all cards from your board and score pile of
  //     color matching the drawn card!

  public function initialExecution()
  {
    $card = self::transferToHand(self::drawAndReveal(11));
    $this->notifications->notifyCardColor(self::getColor($card));
    self::setAuxiliaryValue(self::getColor($card)); // Track color to return
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->return()->all()->fromLocation(Locations::PILE_OR_SCORE)->withColor(self::getAuxiliaryValue());
  }

  public function afterInteraction()
  {
    // Prove that there are no cards of the drawn color left in the score pile
    if (self::countCards(Locations::SCORE) > 0) {
      self::revealScorePile();
    }
  }

}