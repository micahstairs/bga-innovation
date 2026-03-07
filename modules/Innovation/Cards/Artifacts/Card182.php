<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card182 extends AbstractCard
{
  // Singer Model 27
  // - 3rd edition:
  //   - Tuck a card from your hand. If you do, splay up its color, and then tuck all cards from
  //     your score pile of that color.
  // - 4th edition:
  //   - Tuck a card from your hand. If you do, splay up its color, and then tuck all cards from
  //     your score pile of that color. If you do, junk an available standard achievement.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->tuck()->fromYourHand();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->tuck()->all()->fromYourScore()->withColor(self::getLastSelectedColor())->revealingIfUnable();
    } else {
      return self::youMust()->junk()->fromAvailableAchievements();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::splayUp(self::getColor($card));
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::setMaxSteps(3);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}