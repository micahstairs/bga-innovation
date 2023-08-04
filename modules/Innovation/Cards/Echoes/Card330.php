<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card330 extends Card
{

  // Dice
  // - 3rd edition:
  //   - Draw and reveal a [1]. If the card has a bonus, draw and meld a card of value equal to its bonus.
  // - 4th edition:
  //   - Draw and reveal an Echoes [1]. If the card has a bonus, draw and meld a card of value equal to its bonus.
  //   - If Dice was foreseen, draw a [4], then transfer it to the hand of an opponent with more bonus points than you.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      if (self::isFirstOrThirdEdition()) {
        $card = self::drawAndReveal(1);
      } else {
        $card = $this->game->executeDraw(self::getPlayerId(), 1, 'revealed', /*bottom_to=*/ false, $this->game::ECHOES);
      }
      self::transferToHand($card);
      $bonus = self::getBonusIcon($card);
      if ($bonus > 0) {
          self::drawAndMeld($bonus);
      }
    } else if (self::wasForeseen()) {
      $card = self::draw(4);
      $bonusPoints = $this->game->countBonusPoints(self::getPlayerId());
      $opponents = [];
      foreach (self::getOpponentIds() as $otherPlayerId) {
        if ($this->game->countBonusPoints($otherPlayerId) > $bonusPoints) {
          $opponents[] = $this->game->playerIdToPlayerIndex($otherPlayerId);
        }
      }
      if (count($opponents) > 0) {
        self::setAuxiliaryArray($opponents); // Track which opponents can be chosen to receive the card
        self::setAuxiliaryValue2($card['id']); // Track which card needs to be transferred to an opponent's hand
      }
    }
  }

  public function getInteractionOptions(): array
  {
   return [
    'choose_player' => true,
    'players' => self::getAuxiliaryArray(),
   ]; 
  }

  public function handleSpecialChoice(int $opponentId) {
    self::transferToHand(self::getCard(self::getAuxiliaryValue2()), $opponentId);
  }

}