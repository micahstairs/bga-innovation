<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

// 16  => array(
//   'name'                                => clienttranslate('Mathematics'),
//   'non_demand_effect_1_first_and_third' => clienttranslate('You may return a card from your hand. If you do, draw and meld a card of value one higher than the card you returned.'),
//   'non_demand_effect_1_fourth'          => clienttranslate('You may return a card from your hand. If you do, draw and meld a card of value one higher than the card you return.'),
// ),

class Card16 extends AbstractCard
{
  // Mathematics:
  // - 3rd edition:
  //   - You may return a card from your hand. If you do, draw and meld a card of value one higher
  //     than the card you returned.
  // - 4th edition:
  //   - You may return a card from your hand. If you do, draw and meld a card of value one higher
  //     than the card you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->return()->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndMeld(self::getValue($card) + 1);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}