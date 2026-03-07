<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card573 extends AbstractCard
{

  // Clown Car:
  //   - I demand you meld a card from my score pile! If the melded card has
  //     no [PROSPERITY], repeat this effect!

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->fromMyScore();
  }

  public function handleCardChoice(array $card)
  {
    if (!self::hasIcon($card, Icons::PROSPERITY)) {
      self::setNextStep(1);
    }
  }
}