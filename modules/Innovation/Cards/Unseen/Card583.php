<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card583 extends Card
{

  // 3D Printing:
  //   - Return a top or bottom card on your board. Achieve one of your secrets of value equal to
  //     the returned card regardless of eligibility, then safeguard an available standard
  //     achievement. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(4);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      // Skip the first interaction if no color has more than 1 card on the board
      $needsToChoose = false;
      $cardCounts = self::countCardsKeyedByValue('board');
      for ($i = 1; $i <= 11; $i++) {
        if ($cardCounts[$i] > 1) {
          $needsToChoose = true;
          break;
        }
      }
      return ['choices' => $needsToChoose ? [1, 2] : [1]];
    } else if (self::isSecondInteraction()) {
      $returnBottomCard = self::getAuxiliaryValue() === 2;
      self::setAuxiliaryValue(0); // Track which value was returned
      return [
        'location_from'  => 'board',
        'bottom_from'    => $returnBottomCard,
        'return_keyword' => true,
      ];
    } else if (self::isThirdInteraction()) {
      $value = self::getAuxiliaryValue();
      self::setAuxiliaryValue(0); // Track how many cards were transferred in the 2nd sentence of the effect
      return [
        'location_from'   => 'safe',
        'achieve_keyword' => true,
        'age'             => $value,
      ];
    } else {
      return ['safeguard_keyword' => true];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Return top card'),
      2 => clienttranslate('Return bottom card'),
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::setAuxiliaryValue($choice);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::setAuxiliaryValue($card['age']);
    } else {
      self::incrementAuxiliaryValue();
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthInteraction() && self::getAuxiliaryValue() === 2) {
      self::setNextStep(1);
    }
  }

}