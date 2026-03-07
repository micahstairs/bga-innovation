<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card19 extends AbstractCard
{
  // Currency
  // - 3rd edition:
  //   - You may return any number of cards from your hand. If you do, draw and score a [2] for
  //     every different value of card you returned.
  // - 4th edition:
  //   - You may return any number of cards from your hand. If you do, draw and score a [2] for
  //     every different value of card you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
    self::setAuxiliaryValue(Arrays::encode([]));
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->return()->anyNumber()->fromYourHand();
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
      self::drawAndScore(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}