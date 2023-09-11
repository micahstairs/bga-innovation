<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card360 extends Card
{

  // Homing Pigeons
  // - 3rd edition:
  //   - I DEMAND you return two cards from your score pile whose values each match at least one card in my hand!
  //   - You may splay your red or green cards left.
  // - 4th edition:
  //   - I DEMAND you return two cards from your score pile whose values each match a card in my hand!
  //   - You may splay your red or green cards left. If Homing Pigeons was foreseen, splay all your
  //     colors left.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardIds = [];
      $handCounts = self::countCardsKeyedByValue('hand', self::getLauncherId());
      foreach (self::getCards('score') as $card) {
        if ($handCounts[$card['age']] > 0) {
          $cardIds[] = $card['id'];
        }
      }
      self::setMaxSteps(1);
      self::setAuxiliaryArray($cardIds);
    } else {
      self::setMaxSteps(1);
    }

  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'n'                               => 2,
        'location_from'                   => 'score',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::RED, Colors::GREEN],
      ];
    }
  }

  public function afterInteraction(): void
  {
    if (self::isNonDemand() && self::wasForeseen()) {
      // TODO(4E): Does afterInteraction() get called if there are no colors which can be splayed left?
      foreach (Colors::ALL as $color) {
        self::splayLeft($color);
      }
    }
  }

}