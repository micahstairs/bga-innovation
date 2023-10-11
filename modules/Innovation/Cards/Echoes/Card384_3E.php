<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card384_3E extends AbstractCard
{

  // Tuning Fork (3rd edition):
  //   - ECHO: Look at the top card of any deck, then place it back on top.
  //   - Return a card from your hand. If you do, draw and reveal a card of the same value, and
  //     meld it if it is higher than a top card of the same color on your board. Otherwise, return
  //     it. You may repeat this dogma effect.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::countCards('hand') > 0) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      if (self::isFirstInteraction()) {
        $cardIds = [];
        for ($age = 1; $age <= 11; $age++) {
          for ($type = 0; $type <= 5; $type++) {
            $card = $this->game->getDeckTopCard($age, $type);
            if ($card) {
              $cardIds[] = $card['id'];
            }
          }
        }
        self::setAuxiliaryArray($cardIds);
        return [
          'location_from'                   => 'deck',
          'location_to'                     => 'hand',
          'card_ids_are_in_auxiliary_array' => true,
        ];
      } else {
        self::setAuxiliaryArray([self::getLastSelectedId()]);
        return [
          'location_from'                   => 'hand',
          'topdeck_keyword'                 => true,
          'card_ids_are_in_auxiliary_array' => true,
          // Give the player the chance to read the card
          'enable_autoselection'            => false,
        ];
      }
    } else {
      return [
        'can_pass'       => self::isSecondInteraction(),
        'location_from'  => 'hand',
        'return_keyword' => true,
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
      }
      if (self::countCards('hand') > 0) {
        self::setNextStep(1);
      }
    }
  }

}