<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
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
      self::draw(3);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->withoutIcon(Icons::HEALTH)->fromYourBoard();
    } else {
      return self::youMust()->return()->all()->value(self::getAuxiliaryValue2())->fromYourScore();
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