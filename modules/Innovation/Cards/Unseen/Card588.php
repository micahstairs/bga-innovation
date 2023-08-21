<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card588 extends Card
{

  // Dark Web:
  //   - Unsplay any color on any board.
  //   - Choose to either safeguard any number of available standard achievements, or achieve any number of secrets from your safe regardless of eligibility.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'board',
        'location_to'   => 'none',
      ];
    } else {
      if (self::isFirstInteraction()) {
        return ['choices' => [1, 2]];
      } else if (self::getAuxiliaryValue() === 1) {
        return [
          'can_pass'      => true,
          'owner_from'    => 0,
          'location_from' => 'achievements',
          'location_to'   => 'safe',
          'n_min'         => 1,
          'n_max'         => 'all',
        ];
      } else {
        return [
          'can_pass'      => true,
          'location_from' => 'safe',
          'location_to'   => 'achievements',
          'n_min'         => 1,
          'n_max'         => 'all',
        ];
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::getEffectNumber() === 1) {
      self::unsplay($card['color'], $card['owner'], self::getPlayerId());
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Safeguard standard achievements'),
      2 => clienttranslate('Achieve secrets'),
    ]);
  }

  public function handleSpecialChoice(int $choice)
  {
    self::setAuxiliaryValue($choice);
  }

}