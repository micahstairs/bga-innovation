<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card425 extends AbstractCard
{

  // Artificial Heart
  // - 3rd edition 
  //   - Claim one standard achievement, if eligible. Your current score is doubled for the purpose
  //     of checking eligibility.
  // - 4th edition
  //   - Claim one available standard achievement, if eligible, doubling your current score for the
  //     purpose of checking eligibility. If you do, and Artifical Heart was foreseen, repeat this effect.

  public function getInteractionOptions(): array
  {
    $cardIds = [];
    $achievementsByValue = self::getCardsKeyedByValue(Locations::AVAILABLE_ACHIEVEMENTS);
    foreach ($this->game->getClaimableValuesIgnoringAvailability(self::getPlayerId(), 2) as $value) {
      foreach ($achievementsByValue[$value] as $card) {
        $cardIds[] = self::getId($card);
      }
    }
    self::setAuxiliaryArray($cardIds);
    return self::youMust()->achieve()->onlyCardsInAuxiliaryArray()->build();
  }

  public function handleCardChoice(array $card)
  {
    if (self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

}