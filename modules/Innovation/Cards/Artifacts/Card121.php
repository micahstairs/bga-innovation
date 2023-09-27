<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card121 extends AbstractCard
{

  // Xianrendong Shards
  // - 3rd edition:
  //   - Reveal three cards from your hand. Score two, then tuck the other. If you score two cards
  //     of the same color, draw three [1].
  // - 4th edition:
  //   - Reveal three cards from your hand. Score two, then tuck the other. If the scored cards are
  //     the same color, draw three [1].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(-1);
      self::setAuxiliaryValue2(-1);
      return [
        'n'             => 3,
        'location_from' => 'hand',
        'location_to'   => 'revealed'
      ];
    } else {
      return [
        'n'             => 2,
        'location_from' => 'revealed',
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      if (self::getAuxiliaryValue() === -1) {
        self::setAuxiliaryValue($card['color']); // Track color of first scored card
      } else {
        self::setAuxiliaryValue2($card['color']); // Track color of second scored card
      }
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::tuck(self::getRevealedCard());
      $color1 = self::getAuxiliaryValue();
      $color2 = self::getAuxiliaryValue2();
      if ($color1 !== -1 && $color1 === $color2) {
        self::notifyAll(clienttranslate('The scored cards were the same color.'));
        self::draw(1);
        self::draw(1);
        self::draw(1);
      } else {
        self::notifyAll(clienttranslate('The scored cards were not the same color.'));
      }
    }
  }

}