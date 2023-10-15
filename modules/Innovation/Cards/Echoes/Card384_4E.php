<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card384_4E extends AbstractCard
{

  // Tuning Fork (4th edition):
  //   - ECHO: Draw a card of value present in any score pile.
  //   - Foreshadow a card from your hand. If you do, draw and reveal a card of the same value, and
  //     meld it if it is higher than the top card of the same color on your board. If you don't meld
  //     it, return it, and you may repeat this effect.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::countCards('hand') > 0) {
        self::setMaxSteps(1);
        self::setAuxiliaryValue(0); // Track which repetition this is
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $values = [];
      foreach (self::getPlayerIds() as $playerId) {
        $values = array_merge($values, self::getUniqueValues('score', $playerId));
      }
      return [
        'choose_value' => true,
        'age'          => $values,
      ];
    } else {
      return [
        'can_pass'           => self::getAuxiliaryValue() > 0,
        'location_from'      => 'hand',
        'foreshadow_keyword' => true,
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::draw($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isEcho() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    } else if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      $revealedCard = self::drawAndReveal(self::getValue($card));
      $topCard = self::getTopCardOfColor($revealedCard['color']);
      if (self::getValue($revealedCard) > self::getValue($topCard)) {
        self::meld($revealedCard);
      } else {
        self::return($revealedCard);
        if (self::countCards('hand') > 0) {
          self::setNextStep(1);
          self::incrementAuxiliaryValue();
        }
      }
    }
  }

}