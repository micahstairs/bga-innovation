<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card144 extends Card
{

  // Shroud of Turin
  // - 3rd edition:
  //   - Return a card from your hand. If you do, return a top card from your board and a card from
  //     your score pile of the returned card's color. If you did all three, claim an achievement
  //     ignoring eligibility.
  // - 4th edition:
  //   - Return a card from your hand. If you do, return a top card of the same color from your
  //     board and a card of the same color from your score pile. If you do all three, claim an
  //     achievement ignoring eligibility.


  public function initialExecution()
  {
    self::setAuxiliaryValue(0); // Track number of successful interactions
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'revealed,deck',
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from'  => 'board',
        'return_keyword' => true,
        'color'          => [self::getLastSelectedColor()],
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'location_from' => 'score',
        'location_to'   => 'revealed,deck',
        'color'         => [self::getLastSelectedColor()],
      ];
    } else {
      return ['achieve_keyword' => true];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $this->notifications->notifyCardColor($card['color']);
      self::setMaxSteps(3);
    }
    self::incrementAuxiliaryValue();
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction()) {
      if (self::getNumChosen() === 0) {
        // Prove that no cards of the specified color could have been returned
        self::revealScorePile();
      } else if (self::getAuxiliaryValue() === 3) {
        self::setMaxSteps(4);
      }
    }
  }

}