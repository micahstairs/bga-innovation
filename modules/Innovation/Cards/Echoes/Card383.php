<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card383 extends AbstractCard
{

  // Piano
  //   - ECHO: Draw a card of a value present in any player's hand.
  //   - If you have five top cards, each with a different value, return five cards from your score
  //     pile and then draw and score a card of each of your top cards' values in ascending order.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else {
      $values = [];
      foreach (self::getTopCards() as $card) {
        $values[] = $card['faceup_age'];
      }
      if (count(array_unique($values)) === 5) {
        self::setMaxSteps(1);
        self::setAuxiliaryArray($values);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $values = [];
      foreach (self::getPlayerIds() as $playerId) {
        $values = array_merge($values, self::getUniqueValues('hand', $playerId));
      }
      return [
        'choose_value' => true,
        'age'          => $values,
      ];
    } else {
      return [
        'n'              => 5,
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::draw($value);
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $values = self::getAuxiliaryArray();
      sort($values);
      foreach ($values as $value) {
        self::drawAndScore($value);
      }
    }
  }

}