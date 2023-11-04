<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card134_3E extends AbstractCard
{

  // Cyrus Cylinder (3rd edition):
  //   - Choose any other top purple card on any player's board. Execute its non-demand dogma
  //     effects. Do not share them. Splay left a color on any player's board.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isSplayInteraction()) {
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
    if (self::isSplayInteraction()) {
      self::splayLeft($card['color'], $card['owner'], self::getPlayerId());
    } else {
      self::selfExecute($card);
      self::setMaxSteps(2);
    }

  }

  private function isSplayInteraction(): bool
  {
    return self::isSecondInteraction() || self::isPostExecution();
  }

}