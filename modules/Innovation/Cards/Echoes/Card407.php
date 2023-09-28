<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card407 extends AbstractCard
{

  // Bandage
  // - 3rd edition
  //   - ECHO: Meld a card from your hand with a [HEALTH].
  //   - I DEMAND you return the highest card in your score pile for which you do not have a card
  //     of matching value in your hand! Return a top card from you board with a [EFFICIENCY]!
  // - 4th edition
  //   - ECHO: Meld a card from your hand with a [HEALTH].
  //   - I DEMAND you return a card with a [EFFICIENCY] from your score pile! Return a top card
  //     with a [EFFICIENCY] from your board! If you do both, junk an available achievement for
  //     each achievement you have!

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstOrThirdEdition()) {
      self::setMaxSteps(2);
      $scorePileCounts = self::countCardsKeyedByValue(Locations::SCORE);
      $handCounts = self::countCardsKeyedByValue(Locations::HAND);
      for ($i = 11; $i >= 1; $i--) {
        if ($scorePileCounts[$i] > 0 && $handCounts[$i] == 0) {
          self::setAuxiliaryValue($i); // Track value to return
          return;
        }
      }
      // No card needs to be returned, so skip to second interaction
      self::setNextStep(2);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
        'with_icon'     => Icons::HEALTH,
      ];
    } else if (self::isFirstOrThirdEdition()) {
      if (self::isFirstInteraction()) {
        return [
          'location_from'  => Locations::SCORE,
          'return_keyword' => true,
          'age'            => self::getAuxiliaryValue(),
        ];
      } else {
        return [
          'location_from'  => Locations::BOARD,
          'return_keyword' => true,
          'with_icon'      => Icons::EFFICIENCY,
        ];
      }
    } else {
      if (self::isFirstInteraction()) {
        self::setAuxiliaryValue(0); // Keep track of whether the first interaction happened
        return [
          'location_from'  => Locations::SCORE,
          'return_keyword' => true,
          'with_icon'      => Icons::EFFICIENCY,
        ];
      } else if (self::isSecondInteraction()) {
        return [
          'location_from'  => Locations::BOARD,
          'return_keyword' => true,
          'with_icon'      => Icons::EFFICIENCY,
        ];
      } else {
        return [
          'n'                            => self::countCards(Locations::ACHIEVEMENTS),
          'location_from'                => Locations::AVAILABLE_ACHIEVEMENTS,
          'include_special_achievements' => true,
          'junk_keyword'                 => true,
        ];
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFourthEdition() && self::isDemand()) {
      if (self::isFirstInteraction()) {
        self::setAuxiliaryValue(1);
      } else if (self::getAuxiliaryValue() === 1) {
        self::setMaxSteps(3);
      }
    }
  }

}