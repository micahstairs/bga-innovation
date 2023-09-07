<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

class Card564 extends Card
{

  // Opus Dei:
  //   - Reveal the highest card in your score pile. If you do, splay your cards of the revealed
  //     card's color up, and safeguard the revealed card.
  //   - Draw an [8] for each color on your board splayed up.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      $cardIds = $this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), 'score');
      if (count($cardIds) >= 1) {
        self::setAuxiliaryValue(self::getCard($cardIds[0])['age']);
        self::setMaxSteps(1);
      }
    } else {
      foreach (self::getTopCards() as $card) {
        if ($card['splay_direction'] == Directions::UP) {
          self::draw(8);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'score',
      'location_to'   => 'revealed',
      'age'           => self::getAuxiliaryValue(),
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::splayUp($card['color']);
    if (!self::safeguard($card)) {
      self::transferToScorePile($card);
    }
  }

}