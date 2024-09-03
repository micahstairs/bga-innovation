<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card73 extends AbstractCard
{
  // Lighting:
  // - 3rd edition:
  //   - You may tuck up to three cards from your hand. If you do, draw and score a [7] for every
  //     different value of card you tucked.
  // - 4th edition:
  //   - You may tuck up to three cards from your hand. If you do, draw and score a [7] for every
  //     different value of card you tuck.

  public function initialExecution()
  {
    self::setAuxiliaryValue(Arrays::encode([])); // Track the different values of cards tucked
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->tuck()->minCards(1)->maxCards(3)->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    $value = self::getValue($card);
    $differentValues = Arrays::decode(self::getAuxiliaryValue());
    if (!in_array($value, $differentValues)) {
      $differentValues[] = $value;
      self::setAuxiliaryValue(Arrays::encode($differentValues));
    }
  }

  public function afterInteraction()
  {
    $differentValues = Arrays::decode(self::getAuxiliaryValue());
    for ($i = 0; $i < count($differentValues); $i++) {
      self::drawAndScore(7);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}