<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card178_3E extends AbstractCard
{
  // Jedlik's Electromagnetic Self-Rotor (3rd edition):
  //   - Draw and score an [8]. Draw and meld an [8]. Claim an achievement of value 8 if it is
  //     available, ignoring eligibility.

  public function initialExecution()
  {
    self::drawAndScore(8);
    self::drawAndMeld(8);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'achieve_keyword' => true,
      'age' => 8,
    ];
  }

}