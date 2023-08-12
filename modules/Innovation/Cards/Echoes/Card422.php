<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card422 extends Card
{

  // Wristwatch
  // - 3rd edition
  //   - ECHO: Take a non-yellow top card from your board and tuck it.
  //   - For each visible bonus on your board, draw and tuck a card of that value, in ascending order.
  // - 4th edition
  //   - ECHO: Tuck a top card from your board.
  //   - If Wristwatch was foreseen, return all non-bottom cards from your board.
  //   - For each visible bonus on your board, draw and meld a card of that value, in ascending order.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand() && self::isFourthEdition()) {
      if (self::wasForeseen()) {
        self::setMaxSteps(1);
      }
    } else {
      $bonuses = self::getBonuses();
      sort($bonuses);
      foreach ($bonuses as $value) {
        if (self::isFirstOrThirdEdition()) {
          self::drawAndTuck($value);
        } else {
          self::drawAndMeld($value);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $options = [
        'location_from' => 'board',
        'tuck_keyword'  => true,
      ];
      if (self::isFirstOrThirdEdition()) {
        $options['color'] = self::getAllColorsOtherThan($this->game::YELLOW);
      }
      return $options;
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'pile',
        'return_keyword' => true,
      ];
    }
  }

}