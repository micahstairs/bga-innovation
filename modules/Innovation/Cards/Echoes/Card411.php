<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card411 extends Card
{

  // Air Conditioner
  // - 3rd edition
  //   - ECHO: You may score a card from your hand.
  //   - I DEMAND you return all cards from your score pile of value matching any of your top cards!
  // - 4th edition
  //   - ECHO: You may score a card from your hand.
  //   - I DEMAND you return all cards from your score pile of value matching any of your top cards!
  //   - Junk all cards in the [9] deck.

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::junkBaseDeck(9);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
    } else {
      $topCards = self::getTopCards();
      $cardIds = [];
      foreach (self::getCards('score') as $scorePileCard) {
        $found = false;
        foreach ($topCards as $topCard) {
          if ($topCard['faceup_age'] == $scorePileCard['age']) {
            $found = true;
            break;
          }
        }
        if ($found) {
          $cardIds[] = $scorePileCard['id'];
        }
      }
      self::setAuxiliaryArray($cardIds);
      return [
        'n'                               => count(self::getAuxiliaryArray()),
        'location_from'                   => 'score',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

}