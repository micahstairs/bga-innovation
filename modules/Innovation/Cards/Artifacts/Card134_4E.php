<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card134_4E extends AbstractCard
{

  // Cyrus Cylinder (4th edition):
  //   - Splay left a color on any player's board.
  //   - Choose any other top purple card on any player's board. Self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'owner_from'  => 'any player',
        'choose_from' => Locations::BOARD,
      ];
    } else {
      return [
        'owner_from'  => 'any player',
        'choose_from' => Locations::BOARD,
        'color'       => [Colors::PURPLE],
        // Exclude the card currently being executed (it's possible for the effects of Cyrus Cylinder to be executed as if it were on another card)
        'not_id'      => $this->game->getCurrentNestedCardState()['executing_as_if_on_card_id'],
      ];
    }

  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::splayLeft($card['color'], $card['owner'], self::getPlayerId());
    } else {
      self::selfExecute($card);
    }

  }

}