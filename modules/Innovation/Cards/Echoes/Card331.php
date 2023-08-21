<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card331 extends Card
{

  // Perfume
  // - 3rd edition:
  //   - ECHO: Draw and tuck a [1].
  //   - I DEMAND you transfer a top card of different value from any top card on my board from your board to mine! If you do, draw and meld a card of equal value!
  // - 4th edition:
  //   - ECHO: Draw and tuck a [1]. If it has a [AUTHORITY], repeat this effect.
  //   - I DEMAND you transfer a top card of different value from any top card on my board from your board to mine! If you do, draw and meld a card of equal value!

  public function initialExecution()
  {
    if (self::isEcho()) {
      $repeat = true;
      while ($repeat) {
        $repeat = false;
        $card = self::drawAndTuck(1);
        if (self::isFourthEdition() && self::hasIcon($card, $this->game::AUTHORITY)) {
          $repeat = true;
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $colors = [];
    $playerCards = self::getTopCards(self::getPlayerId());
    $launcherCards = self::getTopCards(self::getLauncherId());
    foreach ($playerCards as $playerCard) {
      $matchFound = false;
      foreach ($launcherCards as $launcherCard) {
        if ($playerCard['faceup_age'] == $launcherCard['faceup_age']) {
          $matchFound = true;
          break;
        }
      }
      if (!$matchFound) {
        $colors[] = $playerCard['color'];
      }
    }
    return [
      'owner_from' => self::getPlayerId(),
      'location_from' => 'board',
      'owner_to' => self::getLauncherId(),
      'location_to' => 'board',
      'color' => $colors,
    ];
  }

  public function handleCardChoice(array $card) {
    self::drawAndMeld($card['faceup_age']);
  }

}