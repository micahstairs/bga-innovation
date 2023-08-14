<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card429 extends Card
{

  // GPS
  // - 3rd edition 
  //   - I DEMAND you return all cards from your forecast!
  //   - Draw and foreshadow three [10].
  //   - You may splay your yellow cards up.
  // - 4th edition
  //   - I DEMAND you return all cards from your forecast!
  //   - You may splay your yellow cards up.
  //   - Draw three [11]. If GPS was foreseen, foreshadow them.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow(10);
      self::drawAndForeshadow(10);
      self::drawAndForeshadow(10);
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      $card1 = self::draw(11);
      $card2 = self::draw(11);
      $card3 = self::draw(11);
      if (self::wasForeseen()) {
        self::setAuxiliaryArray([$card1['id'], $card2['id'], $card3['id']]); // Track cards to foreshadow
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'n' => 'all',
        'location_from' => 'forecast',
        'return_keyword' => true,
      ];
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      return [
        'n' => 3,
        'location_from' => 'hand',
        'foreshadow_keyword' => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass' => true,
        'splay_direction' => $this->game::UP,
        'color' => [$this->game::YELLOW],
      ];
    }
  }

}