<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card432 extends Card
{

  // MP3
  // - 3rd edition 
  //   - Return any number of cards from your hand. For each card returned, claim two standard achievements for which you are eligible.
  //   - Draw and score a card of value equal to a bonus on your board.
  // - 4th edition
  //   - ECHO: Draw and score a [10].
  //   - Draw and score a card of value equal to a bonus on your board, if there is one.
  //   - Return any number of cards from your hand. For each card returned, claim two standard achievements for which you are eligible.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndScore(10);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if ((self::isFirstOrThirdEdition() && self::isFirstNonDemand()) || (self::isFourthEdition() && self::isSecondNonDemand())) {
      if (self::isFirstInteraction()) {
        return [
          'can_pass'       => true,
          'n_min'          => 1,
          'n_max'          => 'all',
          'location_from'  => 'hand',
          'return_keyword' => true,
        ];
      } else {
        return [
          'n'                               => self::getAuxiliaryValue(),
          'achieve_keyword'                 => true,
          'require_achievement_eligibility' => true,
        ];
      }
    } else {
      $bonuses = self::getBonuses();
      if (self::isFirstOrThirdEdition() && empty($bonuses)) {
        $bonuses[] = 0;
      }
      return [
        'choose_value' => true,
        'age'          => $bonuses,
      ];
    }
  }

  public function handleSpecialChoice(int $value) {
    self::drawAndScore($value);
  }

  public function afterInteraction() {
    if ((self::isFirstOrThirdEdition() && self::isFirstNonDemand()) || (self::isFourthEdition() && self::isSecondNonDemand())) {
      if (self::getNumChosen() > 0) {
        self::setAuxiliaryValue(self::getNumChosen() * 2); // Track number of achievements to achieve
      }
    }
  }

}