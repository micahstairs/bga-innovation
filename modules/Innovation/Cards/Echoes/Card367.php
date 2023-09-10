<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

class Card367 extends Card
{

  // Kobukson
  // - 3rd edition:
  //   - ECHO: Splay left one color on any player's board.
  //   - I DEMAND you return all your top cards with a [AUTHORITY]! Draw and tuck a [4]!
  //   - For every two cards returned as a result of the demand, draw and tuck a [4].
  // - 4th edition:
  //   - ECHO: Splay left one color on any player's board.
  //   - I DEMAND you return all your top cards with a [AUTHORITY] of each color from your board! Draw and tuck a [4]!
  //   - Draw and tuck a [4].
  //   - If Kobukson was foreseen, draw and meld a [5].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setAuxiliaryValue(0); // Tracks how many cards are returned in the demand
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        $numCards = $this->game->intDivision(self::getAuxiliaryValue(), 2);
        for ($i = 0; $i < $numCards; $i++) {
          self::drawAndTuck(4);
        }
      } else {
        self::drawAndTuck(4);
      }
    } else if (self::wasForeseen()) {
      self::drawAndMeld(5);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'board',
        'location_to'   => 'none',
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'board',
        'return_keyword' => true,
        'with_icon'      => Icons::AUTHORITY,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isEcho()) {
      $this->game->splayLeft(self::getPlayerId(), $card['owner'], $card['color']);
    } else if (self::isDemand()) {
      self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      self::drawAndTuck(4);
    }
  }

}