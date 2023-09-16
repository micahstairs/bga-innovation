<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card414 extends Card
{

  // Television
  //   - ECHO: Draw and meld an [8].
  //   - Choose a value and an opponent. Transfer a card of that value from their score pile to
  //     their board. If they have an achievement of the same value, achieve (if eligible) a card
  //     of that value from their score pile.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndMeld(8);
    } else {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_value' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'choose_player' => true,
        'players'       => $this->game->getActiveOpponents(self::getPlayerId()),
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'owner_from'    => self::getAuxiliaryValue2(),
        'location_from' => 'score',
        'owner_to'      => self::getAuxiliaryValue2(),
        'location_to'   => 'board',
        'age'           => self::getAuxiliaryValue(),
      ];
    } else {
      return [
        'owner_from'          => self::getAuxiliaryValue2(),
        'location_from'       => 'score',
        'achieve_if_eligible' => true,
        'age'                 => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::setAuxiliaryValue($value); // Track value chosen
  }

  public function handlePlayerChoice($playerId)
  {
    self::setAuxiliaryValue2($playerId); // Track opponent chosen
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction()) {
      $value = self::getAuxiliaryValue();
      $opponentId = self::getAuxiliaryValue2();
      if (self::countCardsKeyedByValue('achievements', $opponentId)[$value] > 0) {
        self::setMaxSteps(4);
      }
    }
  }

}