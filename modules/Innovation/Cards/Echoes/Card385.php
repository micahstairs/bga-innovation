<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card385 extends AbstractCard
{

  // Bifocals
  // - 3rd edition
  //   - ECHO: Draw and foreshadow a card of any value.
  //   - You may return a card from your forecast. If you do, draw and foreshadow a card of equal
  //     value to the card returned.
  //   - You may splay your green cards right.
  // - 4th edition
  //   - ECHO: Return a card from your forecast.
  //   - Draw and foreshadow a [7], and then if Bifocals was foreseen, draw and foreshadow a card
  //     of value equal to the lowest available standard achievement.
  //   - You may splay your green cards right. If you do, splay any color of your cards up.

  // TODO(LATER): Split this implementation into separate files for 3rd and 4th edition.

  public function initialExecution()
  {
    if (self::isFourthEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow(7);
      if (self::wasForeseen()) {
        $value = self::getMinValueInLocation(Locations::AVAILABLE_ACHIEVEMENTS);
        self::drawAndForeshadow($value);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::getThirdEditionInteractionOptions();
    } else {
      return self::getFourthEditionInteractionOptions();
    }
  }

  public function getThirdEditionInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choose_value' => true];
    } else if (self::isFirstNonDemand()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::GREEN],
      ];
    }
  }

  public function getFourthEditionInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::GREEN],
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::drawAndForeshadow($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow($card['age']);
    }
  }

  public function afterInteraction() {
    if (self::isFourthEdition() && self::isSecondNonDemand() && self::isFirstInteraction() && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
    }
  }

}