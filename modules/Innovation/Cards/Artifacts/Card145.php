<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

class Card145 extends Card
{

  // Petition of Right
  // - 3rd edition:
  //   - I COMPEL you to transfer a card from your score pile to my score pile for each top card
  //     with a [AUTHORITY] on your board!
  // - 4th edition:
  //   - I COMPEL you to transfer a card from your score pile to my score pile for each color with
  //     a [AUTHORITY] on your board!
  //   - Junk an available achievement of value equal to the number of [AUTHORITY] on your board.


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $numCardsWithAuthority = 0;
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        $numCardsWithAuthority++;
      }
    }
    if (self::isCompel()) {
      return [
        'n'             => $numCardsWithAuthority,
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'score',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'score'
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'junk_keyword'  => true,
        'age'           => $numCardsWithAuthority,
      ];
    }
  }

}