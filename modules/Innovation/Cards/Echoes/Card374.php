<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card374 extends Card
{

  // Toilet
  // - 3rd edition:
  //  - ECHO: Draw and tuck a [4].
  //  - I DEMAND you return all cards from your score pile of value matching the highest bonus on my board!
  //  - You may return a card in your hand and draw a card of the same value.
  // - 4th edition:
  //  - ECHO: Draw and tuck a [4].
  //  - I DEMAND you return all cards from your score pile of the highest value matching a bonus on my board!
  //  - You may return a card in your hand and draw a card of the same value.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(4);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition()) {
        $value = $this->game->getMaxBonusIconOnBoard(self::getLauncherId());
      } else {
        $bonuses = $this->game->getVisibleBonusesOnBoard(self::getLauncherId());
        $scorePileValues = self::getUniqueValues('score');
        $intersection = array_intersect($bonuses, $scorePileValues);
        if (!$intersection) {
          return [];
        }
        $value = max($intersection);
      }
      return [
        'n'              => 'all',
        'location_from'  => 'score',
        'return_keyword' => true,
        'age'            => $value,
      ];
    } else {
      return [
        'can_pass'       => true,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isNonDemand()) {
      self::draw($card['age']);
    }
  }

}