<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card402 extends Card
{

  // Fertilizer
  // - 3rd edition
  //   - You may return a card from your hand. If you do, transfer all cards from all score piles to your hand of value equal to the returned card.
  //   - Draw and foreshadow a card of any value.
  // - 4th edition
  //   - You may return a card from your hand. If you do, transfer all cards from all score piles to your hand of value equal to the returned card.
  //   - Draw and foreshadow a card of value equal to the number of cards in your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::isFirstOrThirdEdition()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndForeshadow(self::countCards('hand'));
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return ['choose_value' => true];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstNonDemand()&& self::getCurrentStep() === 1) {
      $value = $card['age'];
      foreach (self::getPlayerIds() as $playerId) {
        foreach (self::getCardsKeyedByValue('score', $playerId)[$value] as $scoreCard) {
          self::putInHand($scoreCard);
        }
      }
    }
  }

  public function handleSpecialChoice($value) {
    self::drawAndForeshadow($value);
  }
  
}