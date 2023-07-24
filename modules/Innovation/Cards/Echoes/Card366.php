<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card366 extends Card
{

  // Telescope
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [5].
  //   - You may place a card from your forecast on top of its deck. If you do, achieve a card from
  //     your forecast if you meet the requirements to do so.
  // - 4th edition:
  //   - ECHO: Draw and foreshadow an Echoes [5].
  //   - You may place a card from your forecast on top of its deck. If you do, exchange all cards
  //     in your forecast with an equal number of available standard achievements, and junk all the
  //     cards in the [5] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(5);
      } else {
        self::foreshadow(self::drawFromSet(5, $this->game::ECHOES));
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass' => true,
              'location_from' => 'forecast',
              'location_to' => 'deck',
              'bottom_to' => false, // put on top
      ];
    } else if (self::isFirstOrThirdEdition()){
      return [
        'location_from' => 'forecast',
        'achieve_keyword' => true,
        'require_achievement_eligibility' => true,
      ];
    } else {
      self::setAuxiliaryArray(self::getAvailableStandardAchievementIds());
      $forecastCards = self::getCards('forecast');
      foreach ($forecastCards as $card) {
        $this->game->transferCardFromTo($card, 0, 'achievements');
      }
      return [
        'n' => count($forecastCards),
        'owner_from' => 0,
        'location_from' => 'achievements',
        'location_to' => 'forecast',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function afterInteraction() {
    if (self::getCurrentStep() === 1 && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
    } else if (self::isFourthEdition() && self::getCurrentStep() === 2) {
      self::junk(5);
    }
  }

  public function handleAbortedInteraction() {
    // Still junk the cards in the [5] deck even if there were no cards to exchange
    if (self::isFourthEdition() && self::getCurrentStep() === 2) {
      self::junk(5);
    }
  }

  private function getAvailableStandardAchievementIds(): array {
    $cardIds = [];
      foreach (self::getCards('achievements', 0) as $card)  {
        if (self::isValuedCard($card)) {
          $cardIds[] = $card['id'];
        }
      }
      return $cardIds;
  }

}
