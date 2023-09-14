<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card448 extends Card
{

  // Escapism:
  //   - Reveal and junk a card in your hand. Return from your hand all cards of value equal to the
  //     value of the junked card. Draw three cards of that value. Self-execute the junked card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_from' => Locations::HAND];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
        'age'            => self::getLastSelectedFaceUpAge(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $this->game->revealCardWithoutMoving(self::getPlayerId(), $card);
      self::junk($card);
      self::setAuxiliaryValue($card['id']);
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $card = self::getCard(self::getAuxiliaryValue());
      self::draw($card['faceup_age']);
      self::draw($card['faceup_age']);
      self::draw($card['faceup_age']);
      self::selfExecute($card);
    }
  }

}