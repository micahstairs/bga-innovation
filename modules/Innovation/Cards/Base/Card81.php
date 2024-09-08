<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card81 extends AbstractCard
{
  // Antibiotics:
  // - 3rd edition:
  //   - You may return up to three cards from your hand. For every different value of card that you
  //     returned, draw two [8].
  // - 4th edition:
  //   - You may return up to three cards from your hand. For every different value of card that you
  //     return, draw two [8].

  public function initialExecution()
  {
    self::setAuxiliaryValue(Arrays::encode([])); // Keep track of which values have been returned
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->return()->minCards(1)->maxCards(3)->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    $value = self::getValue($card);
    $values = Arrays::decode(self::getAuxiliaryValue());
    if (!in_array($value, $values)) {
      $values[] = $value;
    }
    self::setAuxiliaryValue(Arrays::encode($values));
  }

  public function afterInteraction()
  {
    $values = Arrays::decode(self::getAuxiliaryValue());
    for ($i = 0; $i < count($values); $i++) {
      self::draw(8);
      self::draw(8);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}