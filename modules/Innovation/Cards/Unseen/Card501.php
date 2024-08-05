<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card501 extends AbstractCard
{
  // Exile:
  //   - I DEMAND you return a top card without [HEALTH] from your board! Return all cards of the
  //     returned card's value from your score pile!
  //   - If exactly one card was returned due to the demand, return Exile if it is a top card on any
  //     board and draw a [3].

  public function oneTimeSetup()
  {
    self::setAuxiliaryValue(0); // Used to track the number of cards returned due to the demand
  }

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(2);
      self::setAuxiliaryValue2(0); // Used to track the value of the returned top card
    } else if (self::isFirstNonDemand() && self::getAuxiliaryValue() == 1) {
      if ($card = $this->game->getIfTopCardOnBoard(CardIds::EXILE)) {
        self::return($card);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'  => Locations::BOARD,
        'without_icon'   => Icons::HEALTH,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from' => Locations::SCORE,
        'age'           => self::getAuxiliaryValue2(),
        'n'             => 'all',
        'return_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue2(self::getValue($card));
    }
  }

}