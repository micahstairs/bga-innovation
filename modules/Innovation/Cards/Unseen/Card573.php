<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card573 extends AbstractCard
{

  // Clown Car:
  //   - I demand you meld a card from my score pile! If the melded card has
  //     no [PROSPERITY], repeat this effect!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => 'score',
      'owner_to'      => self::getPlayerId(),
      'meld_keyword'  => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (!self::hasIcon($card, Icons::PROSPERITY)) {
      self::setNextStep(1);
    }
  }
}