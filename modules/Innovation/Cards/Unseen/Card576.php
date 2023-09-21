<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card576 extends AbstractCard
{

  // Inhomogeneous Cosmology:
  //   - You may place a top card from your board on top of its deck. You may meld a card from your
  //     hand. If you do either, repeat this effect.
  //   - Draw an [11] for every color not on your board.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(2);
    } else {
      $numColorsNotOnBoard = 5 - count(self::getTopCards());
      for ($i = 0; $i < $numColorsNotOnBoard; $i++) {
        self::draw(11);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(0); // Track whether cards were transferred as part of either interaction
      return [
        'can_pass' => true,
        'location_from' => 'board',
        'location_to'   => 'deck',
        'bottom_to' => false, // Topdeck'
      ];
    } else {
      return [
        'can_pass' => true,
        'location_from' => 'hand',
        'meld_keyword'   => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    self::setAuxiliaryValue(1);
  }

  public function afterInteraction() {
    if (self::isSecondInteraction() && self::getAuxiliaryValue() == 1) {
      self::setNextStep(1);
    }
  }

}