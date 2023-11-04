<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card422_4E extends AbstractCard
{

  // Wristwatch (4th edition):
  //   - ECHO: Tuck a top card from your board.
  //   - If Wristwatch was foreseen, return all non-bottom cards from your board.
  //   - For each bonus on your board, draw and meld a card of that value, in ascending order.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::wasForeseen()) {
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      $bonuses = self::getBonuses();
      sort($bonuses);
      foreach ($bonuses as $value) {
        self::drawAndMeld($value);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => Locations::BOARD,
        'tuck_keyword'  => true,
      ];
    } else {
      $cardIds = [];
      foreach (self::getCardsKeyedByColor(Locations::BOARD) as $stack) {
        foreach ($stack as $card) {
          if ($card['position'] > 0) {
            $cardIds[] = $card['id'];
          }
        }
      }
      self::setAuxiliaryArray($cardIds);
      return [
        'n'                               => 'all',
        'location_from'                   => Locations::PILE,
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

}