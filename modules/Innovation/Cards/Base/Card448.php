<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card448 extends AbstractCard
{

  // Escapism:
  //   - Reveal and junk a card in your hand. Return from your hand all cards of value equal to the
  //     value of the junked card. Draw three cards of that value. Self-execute the junked card.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(-1); // Track the ID of the junked card
      return self::youMust()->chooseCardFrom(Locations::HAND)->build();
    } else {
      return self::youMust()->return()->all()->fromYourHand()->value(self::getLastSelectedFaceUpAge())->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $this->game->revealCardWithoutMoving(self::getPlayerId(), $card);
      self::junk($card);
      self::setAuxiliaryValue(self::getId($card));
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $card = self::getCard(self::getAuxiliaryValue());
      $value = self::getFaceupValue($card);
      self::draw($value);
      self::draw($value);
      self::draw($value);
      self::selfExecute($card);
    }
  }

}