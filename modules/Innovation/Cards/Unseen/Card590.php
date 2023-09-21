<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card590 extends AbstractCard
{

  // Mystery Box:
  //   - Claim an available standard achievement, regardless of eligibility. Self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'achieve_keyword' => true,
      'age_min'         => 1,
      'age_max'         => 11,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

}