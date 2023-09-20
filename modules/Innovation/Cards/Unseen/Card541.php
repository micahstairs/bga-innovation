<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card541 extends Card
{

  // Attic:
  //   - You may score or safeguard a card from your hand.
  //   - Return a card from your score pile.
  //   - Draw and score a card of value equal to a card in your score pile.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isThirdNonDemand()) {
      if (self::countCards('score') >= 1) {
        self::setMaxSteps(1);
      } else {
        self::drawAndScore(0);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::getFirstInteractionOptions();
    } else if (self::isSecondNonDemand()) {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return [
        'choose_value' => true,
        'age'          => self::getUniqueValues('score'),
      ];
    }
  }

  private function getFirstInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [0, 1],
      ];
    } else {
      $keyword = self::getAuxiliaryValue() == 1 ? 'score_keyword' : 'safeguard_keyword';
      return [
        'location_from' => 'hand',
        $keyword        => true,
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Safeguard a card from your hand'),
      1 => clienttranslate('Score a card from your hand'),
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::setAuxiliaryValue($choice);
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndScore($value);
  }

}