<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card590 extends AbstractCard
{

  // Mystery Box:
  //   - Claim an available standard achievement, regardless of eligibility. Self-execute it.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->achieve()->range(1, 11);
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

}