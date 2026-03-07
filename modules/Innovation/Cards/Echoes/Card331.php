<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card331 extends AbstractCard
{

  // Perfume
  // - 3rd edition:
  //   - ECHO: Draw and tuck a [1].
  //   - I DEMAND you transfer a top card of different value from any top card on my board from
  //     your board to mine! If you do, draw and meld a card of equal value!
  // - 4th edition:
  //   - ECHO: Draw and tuck a [1]. If it has [AUTHORITY], repeat this effect.
  //   - I DEMAND you transfer a top card of different value from any top card on my board from
  //     your board to mine! If you do, draw and meld a card of equal value!

  public function initialExecution()
  {
    if (self::isEcho()) {
      $repeat = true;
      while ($repeat) {
        $repeat = false;
        $card = self::drawAndTuck(1);
        if (self::isFourthEdition() && self::hasIcon($card, Icons::AUTHORITY)) {
          $repeat = true;
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $colors = [];
    $playerCards = self::getTopCards(self::getPlayerId());
    $launcherCards = self::getTopCards(self::getLauncherId());
    foreach ($playerCards as $playerCard) {
      $matchFound = false;
      foreach ($launcherCards as $launcherCard) {
        if (self::getFaceupValue($playerCard) == self::getFaceupValue($launcherCard)) {
          $matchFound = true;
          break;
        }
      }
      if (!$matchFound) {
        $colors[] = self::getColor($playerCard);
      }
    }
    return self::youMust()->withColor($colors)->fromYourBoard()->toMyBoard();
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndMeld(self::getFaceupValue($card));
  }

}