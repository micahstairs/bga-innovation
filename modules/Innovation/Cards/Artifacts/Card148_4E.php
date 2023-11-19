<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card148_4E extends AbstractCard
{

  // Galleon Nuestra Señora De Atocha (4th edition):
  //   - I COMPEL you to transfer all the cards of the value of my choice from your score pile to
  //     my score pile! If you transfer any, transfer a top card on your board of that value to my board!


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'player_id' => self::getLauncherId(),
        'choose_value' => true,
      ];
    } else {
      return [
        'location' => Locations::BOARD,
        'owner_to' => self::getLauncherId(),
        'age'      => self::getAuxiliaryValue(),
      ];
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

}