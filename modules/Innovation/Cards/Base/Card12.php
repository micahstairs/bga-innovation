<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card12 extends AbstractCard
{
  // City States
  // - 3rd edition:
  //   - I DEMAND you transfer a top card with a [AUTHORITY] from your board to my board if you
  //     have at least four [AUTHORITY] on your board! If you do, draw a [1]!
  // - 4th edition:
  //   - I DEMAND you transfer a top card with [AUTHORITY] from your board to my board if you
  //     have four [AUTHORITY] on your board! If you do, draw a [1]!

  public function initialExecution()
  {
    $args = ['icon' => Icons::render(Icons::AUTHORITY)];
    if (self::getStandardIconCount(Icons::AUTHORITY) >= 4) {
      self::notifyPlayer(clienttranslate('${You} have at least four ${icon} on your board.'), $args);
      self::notifyOthers(clienttranslate('${player_name} has at least four ${icon} on his board.'), $args);
      self::setMaxSteps(1);
    } else {
      self::notifyPlayer(clienttranslate('${You} have less than four ${icon} on your board.'), $args);
      self::notifyOthers(clienttranslate('${player_name} has less than four ${icon} on his board.'), $args);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->fromYourBoard()->withIcon(Icons::AUTHORITY)->toMine();
  }

  public function handleCardChoice(array $card)
  {
    self::draw(1);
  }

  public function demandMightBeEffective(): bool
  {
    if (self::getStandardIconCount(Icons::AUTHORITY) < 4) {
      return false;
    }
    return count(self::filterByIcon(self::getTopCards(), Icons::AUTHORITY)) > 0;
  }

}