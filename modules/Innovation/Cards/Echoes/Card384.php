<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card384 extends Card
{

  // Tuning Fork
  // - 3rd edition
  //   - ECHO: Look at the top card of any deck, then place it back on top.
  //   - Return a card from your hand. If you do, draw and reveal a card of the same value, and
  //     meld it if it is higher than a top card of the same color on your board. Otherwise, return
  //     it. You may repeat this dogma effect.
  // - 4th edition
  //   - ECHO: Draw a card of value present in any score pile.
  //   - Foreshadow a card from your hand. If you do, draw and reveal a card of the same value, and
  //     meld it if it is higher than the top card of the same color on your board. If you don't meld
  //     it, return it, and you may repeat this effect.

  public function initialExecution()
  {
    if (self::countCards('hand') > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        return self::getThirdEditionEchoInteractionOptions();
      } else {
        return self::getFourthEditionEchoInteractionOptions();
      }
    } else {
      $keyword = self::isFirstOrThirdEdition() ? 'return_keyword' : 'foreshadow_keyword';
      return [
        'can_pass'      => self::isSecondInteraction(),
        'location_from' => 'hand',
        $keyword        => true,
      ];
    }
  }

  private function getThirdEditionEchoInteractionOptions(): array
  {
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
      return [
        'location_from'        => 'hand',
        'topdeck_keyword'      => true,
        'card_id_1'            => self::getLastSelectedId(),
        'enable_autoselection' => false, // Give the player the chance to read the card
      ];
    }
  }

  private function getFourthEditionEchoInteractionOptions(): array
  {
    $values = [];
    foreach (self::getPlayerIds() as $playerId) {
      $values = array_merge($values, self::getUniqueValues('score', $playerId));
    }
    return [
      'choose_value' => true,
      'age'          => $values,
    ];
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
      $revealedCard = self::drawAndReveal($card['age']);
      $topCard = self::getTopCardOfColor($revealedCard['color']);
      if (!$topCard || $revealedCard['faceup_age'] > $topCard['faceup_age']) {
        self::meld($revealedCard);
        if (self::isFirstOrThirdEdition() && self::countCards('hand') > 0) {
          self::setNextStep(1);
        }
      } else {
        self::return($revealedCard);
        if (self::countCards('hand') > 0) {
          self::setNextStep(1);
        }
      }
    }
  }

}