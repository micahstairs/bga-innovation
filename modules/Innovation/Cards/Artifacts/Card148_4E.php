<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card148_4E extends AbstractCard
{

  // Galleon Nuestra Señora De Atocha (4th edition):
  //   - I COMPEL you to transfer all the cards of the value of my choice from your score pile to
  //     my score pile! If you transfer any, transfer a top card on your board of that value to my board!


  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue()->ofMyChoice();
    } else {
      return self::youMust()->value(self::getAuxiliaryValue())->fromYourBoard()->toMine();
    }
  }

  public function handleValueChoice(int $value)
  {
    $transferredCards = false;
    foreach (self::getCards(Locations::SCORE) as $card) {
      if (self::getValue($card) == $value) {
        self::transferToScorePile($card, self::getLauncherId());
        $transferredCards = true;
      }
    }
    if ($transferredCards) {
      self::setAuxiliaryValue($value); // Track values to transfer
      self::setMaxSteps(2);
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::SCORE) > 0;
  }

}