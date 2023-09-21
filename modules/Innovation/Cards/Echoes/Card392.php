<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card392 extends AbstractCard
{

  // Morphine
  // - 3rd edition
  //   - ECHO: Score an odd-valued card from your hand.
  //   - I DEMAND you return all odd-valued cards in your hand! Draw a [6]!
  //   - Draw a card of value one higher than the highest card returned due to the demand, if any were returned.
  //   - You may splay your red cards right.
  // - 4th edition
  //   - ECHO: Score an odd-valued card from your hand.
  //   - I DEMAND you return all odd-valued cards in your hand! Draw a [6]!
  //   - Draw a card of value one higher than the highest card returned due to the demand.
  //   - You may splay your red cards right.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setAuxiliaryValue(0); // Track max value returned during demand
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $maxValueReturned = self::getAuxiliaryValue();
      if (self::isFourthEdition() || $maxValueReturned > 0) {
        self::draw($maxValueReturned + 1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      self::setAuxiliaryArray(self::getOddValuedCardIds(self::getCards('hand')));
      return [
        'location_from'                   => 'hand',
        'score_keyword'                   => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else if (self::isDemand()) {
      $cardIds = self::getOddValuedCardIds(self::getCards('hand'));
      self::setAuxiliaryArray($cardIds);
      return [
        'n'                               => count($cardIds),
        'location_from'                   => 'hand',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::RED],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isDemand()) {
      $maxValue = max(self::getAuxiliaryValue(), $card['age']);
      self::setAuxiliaryValue($maxValue);
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      self::draw(6);
    }
  }

  public function handleAbortedInteraction()
  {
    if (self::isDemand()) {
      // If no odd cards were returned, a [6] still needs to be drawn
      self::draw(6);
    }
  }

  private function getOddValuedCardIds(array $cards): array
  {
    $cardIds = [];
    foreach ($cards as $card) {
      if ($card['age'] % 2 == 1) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}