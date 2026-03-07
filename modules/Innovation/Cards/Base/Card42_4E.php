<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card42_4E extends AbstractCard
{
  // Perspective (4th edition):
  //   - You may return a card from your hand. If you do, score a card from your hand for every
  //     color on your board with [CONCEPT].

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->return()->fromYourHand();
    } else {
      $numCardsToScore = 0;
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::CONCEPT) > 0) {
          $numCardsToScore++;
        }
      }
      return self::youMay()->score()->exactly($numCardsToScore)->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}