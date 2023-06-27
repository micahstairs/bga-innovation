<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card564 extends Card
{

  // Opus Dei:
  //   - Reveal the highest card in your score pile. If you do, splay your cards of the revealed
  //     card's color up, and safeguard the revealed card.
  //   - Draw an [8] for each color on your board splayed up.

  public function initialExecution()
  {
    if (self::getEffectNumber() == 1) {
      $cardIds = $this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), 'score');
      if (count($cardIds) >= 1) {
        self::setAuxiliaryValue(self::getCard($cardIds[0])['age']);
        self::setMaxSteps(1);
      }
    } else {
      foreach ($this->game->getTopCardsOnBoard(self::getPlayerId()) as $card) {
        if ($card['splay_direction'] == $this->game::UP) {
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

  public function handleCardChoice($cardId)
  {
    self::splayUp(self::getLastSelectedColor());
    $card = self::getCard($cardId);
    if (!self::safeguard($card)) {
      self::transferToScorePile($card);
    }
  }

}