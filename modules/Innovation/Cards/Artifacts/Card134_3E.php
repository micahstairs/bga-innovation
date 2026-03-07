<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
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

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isSplayInteraction()) {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer();
    } else {
      // Exclude the card currently being executed (it's possible for the effects of Cyrus Cylinder to be executed as if it were on another card)
      $excludedCardId = $this->game->getCurrentNestedCardState()['executing_as_if_on_card_id'];
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->withColor(Colors::PURPLE)->otherThan($excludedCardId);
    }

  }

  public function handleCardChoice(array $card)
  {
    if (self::isSplayInteraction()) {
      self::splayLeft(self::getColor($card), self::getOwner($card), self::getPlayerId());
    } else {
      self::selfExecute($card);
      self::setMaxSteps(2);
    }

  }

  private function isSplayInteraction(): bool
  {
    return self::isSecondInteraction() || self::isPostExecution();
  }

  public function nonDemandsMightBeEffective(): bool
  {
    // There are situations where this is not effective, but it's a complicated check and the vast
    // majority of the time it will be effective.
    return true;
  }

}
