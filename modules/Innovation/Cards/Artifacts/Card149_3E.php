<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card149_3E extends AbstractCard
{

  // Molasses Reef Caravel (3rd edition):
  //   - Return all cards from your hand. Draw three [4]. Meld a blue card from your hand. Score a
  //     card from your hand. Return a card from your score pile.


  public function initialExecution()
  {
    self::setMaxSteps(4);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->all()->fromYourHand()->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->meld()->withColor(Colors::BLUE)->fromYourHand()->build();
    } else if (self::isThirdInteraction()) {
      return self::youMust()->score()->fromYourHand()->build();
    } else {
      return self::youMust()->return()->fromYourScore()->build();
    }
  }

}