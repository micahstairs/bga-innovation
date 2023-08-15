<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card425 extends Card
{

  // Artificial Heart
  // - 3rd edition 
  //   - Claim one standard achievement, if eligible. Your current score is doubled for the purpose
  //     of checking eligibility.
  // - 4th edition
  //   - Claim one standard achievement, if eligible, doubling your current score for the purpose of
  //     checking eligibility. If you do, and Artifical Heart was foreseen, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $cardIds = [];
    $achievementsByValue = self::getCardsKeyedByValue('achievements', 0);
    foreach ($this->game->getClaimableValuesIgnoringAvailability(self::getPlayerId(), 2) as $value) {
      foreach ($achievementsByValue[$value] as $card) {
        $cardIds[] = $card['id'];
      }
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'achieve_keyword'                 => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

}