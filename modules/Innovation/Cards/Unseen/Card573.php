<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card573 extends Card
{

  // Clown Car:
  //   - I demand you transfer a card from my score pile to your board! If the transferred card has
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
      'owner_to'    => self::getPlayerId(),
      'location_to'   => 'board',
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (!self::hasIcon($card, $this->game::PROSPERITY)) {
      self::setNextStep(1);
    }
  }
}