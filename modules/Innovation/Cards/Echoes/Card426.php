<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card426 extends Card
{

  // Human Genome
  // - 3rd edition 
  //   - You may draw and score a card of any value. Take a bottom card from your board into your
  //     hand. If the values of all of the cards in your hand match the values of all the cards in
  //     your score pile exactly, you win.
  // - 4th edition
  //   - You may draw and score a card of any value. Transfer your bottom red card to your hand. If
  //     the values of all the cards in your hand match the values of all the cards in your score
  //     pile exactly, you win.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass' => true,
        'choose_value' => true,
      ];
    } else {
      $options = [
        'location_from' => 'board',
        'location_to' => 'hand',
        'bottom_from' => true,
      ];
      if (self::isFourthEdition()) {
        $options['color'] = [$this->game::RED];
      }
      return $options;
    }
  }

  public function handleSpecialChoice(int $value)
  {
    self::drawAndScore($value);
  }

  public function afterInteraction() {
    if (self::isSecondInteraction()) {
      $handCounts = self::countCardsKeyedByValue('hand');
      $scoreCounts = self::countCardsKeyedByValue('score');
      $eligible = true;
      for ($i = 1; $i <= 11; $i++) {
        if ($handCounts[$i] != $scoreCounts[$i]) {
          $eligible = false;
          break;
        }
      }
      if ($eligible) {
        self::win();
      }
    }
  }

}