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
    if (self::getEffectNumber() == 1) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() == 1) {
      return [
        'owner_from' => 'any player',
        'location_from' => 'board',
        'location_to' => 'none',
      ];
    } else if (self::getCurrentStep() == 1) {
      return ['choices' => [1, 2]];
    } else if (self::getAuxiliaryValue() == 1) {
      return [
        'owner_from' => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
        'n_min' => 0,
        'n_max' => 'all',
      ];
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'achievements',
        'n_min' => 0,
        'n_max' => 'all',
      ];
    }
  }

  public function handleCardChoice(int $cardId)
  {
    $card = self::getCard($cardId);
    self::unsplay($card['color'], $card['owner'], self::getPlayerId());
  }

  public function getSpecialChoicePrompt(): array
  {
    if (self::getCurrentStep() == 1) {
      return self::getPromptForChoiceFromList([
        1 => clienttranslate('Safeguard standard achievements'),
        2 => clienttranslate('Achieve secrets'),
      ]);
    } else {
      return self::getPromptForValueChoice();
    }
  }

  public function handleSpecialChoice($choice)
  {
    self::setAuxiliaryValue($choice);
  }

}