<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card511 extends AbstractCard
{
  // Freemasons
  //   - For each color, you may tuck a card from your hand of that color. If you tuck a yellow
  //     card or an expansion card, draw two [3].
  //   - You may splay your yellow or blue cards left.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryValue(0); // Track whether a yellow or expansion card was tucked
      self::setAuxiliaryValue2(Arrays::encode([0, 1, 2, 3, 4])); // Track which colors can still be chosen
      // TODO(4E): Add an "afterEffect" function instead of using a fake interaction.
      self::setMaxSteps(2); // The second interaction is fake, but we need it to be able to execute something at the end of the effect
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'can_pass'      => true,
          'location_from' => Locations::HAND,
          'tuck_keyword'  => true,
          'color'         => Arrays::decode(self::getAuxiliaryValue2()),
        ];
      } else {
        if (self::getAuxiliaryValue() === 1) {
          self::draw(3);
          self::draw(3);
        }
        return [];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::BLUE, Colors::YELLOW],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      if ($card['color'] == Colors::YELLOW || $card['type'] != CardTypes::BASE) {
        self::setAuxiliaryValue(1); // Remember that a yellow card or an expansion card was tucked
      }
      $colors = Arrays::removeElement(Arrays::decode(self::getAuxiliaryValue2()), $card['color']);
      if ($colors) {
        self::setNextStep(1);
        self::setAuxiliaryValue2(Arrays::encode($colors));
      }
    }
  }

}