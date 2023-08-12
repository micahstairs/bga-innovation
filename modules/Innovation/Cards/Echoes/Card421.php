<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card421 extends Card
{

  // ATM
  // - 3rd edition
  //   - ECHO: Draw and score a card of any value.
  //   - I DEMAND you transfer the highest top non-yellow card without a [PROSPERITY] from your board to my board!
  //   - You may splay your purple cards up.
  // - 4th edition
  //   - ECHO: Draw and score a card of any value.
  //   - I DEMAND you transfer your highest top non-yellow card without a [PROSPERITY] to my board!
  //   - You may splay your purple cards up.
  //   - Junk all cards in the [10] deck.

  public function initialExecution()
  {
    if (self::isSecondNonDemand()) {
      self::junkBaseDeck(10);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choose_value' => true];
    } else if (self::isDemand()) {
      $nonYellow = self::getAllColorsOtherThan($this->game::YELLOW);
      return [
        'location_from' => 'board',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'board',
        'age'           => $this->game->getMaxAgeOnBoardOfColorsWithoutIcon(self::getPlayerId(), $nonYellow, $this->game::PROSPERITY),
        'color'         => $nonYellow,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::PURPLE],
      ];
    }
  }

  public function handleSpecialChoice(int $value)
  {
    self::drawAndScore($value);
  }

}