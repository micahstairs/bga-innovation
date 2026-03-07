<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card198 extends AbstractCard
{
  // Velcro Shoes
  // - 3rd edition:
  //   - I COMPEL you to transfer a [9] from your hand to my hand! If you do not, transfer a [9]
  //     from your score pile to my score pile! If you do neither, I win!
  // - 4th edition:
  //   - I COMPEL you to transfer a [9] from your hand to my hand! If you don't, transfer a [9]
  //     from your score pile to my score pile! If you do neither, I win!
  //   - Score your highest top card.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isCompel()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->value(9)->fromYourHand()->toMine();
      } else {
        return self::youMust()->value(9)->fromYourScore()->toMine();
      }
    } else {
      $value = self::getMaxValue(self::getTopCards());
      return self::youMust()->score()->fromYourBoard()->value($value);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() == 0) {
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction() && self::getNumChosen() == 0) {
      self::notifyAll(clienttranslate('Neither transfer took place.'));
      self::win(self::getLauncherId());
    }
  }

}