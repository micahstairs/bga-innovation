<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card535 extends Card
{

  // Placebo:
  //   - Return one or more top cards of one color from your board, from the top. Draw a [7] for
  //     each card you return. If you return exactly one [7], draw an [8].

  public function initialExecution()
  {
    self::setAuxiliaryValue(0); // Used to track the total number of cards returned
    self::setAuxiliaryValue2(0); // Used to track the number of 7s returned
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'location_from' => 'board',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'can_pass'      => true,
        'location_from' => 'board',
        'location_to'   => 'deck',
        'color'         => [self::getLastSelectedColor()],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
    if ($card['age'] == 7) {
      self::setAuxiliaryValue2(self::getAuxiliaryValue2() + 1);
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::setNextStep(2);
    } else if (self::getCurrentStep() == 2) {
      for ($i = 0; $i < self::getAuxiliaryValue(); $i++) {
        self::draw(7);
      }
      if (self::getAuxiliaryValue2() == 1) {
        self::draw(8);
      }
    }
  }

}