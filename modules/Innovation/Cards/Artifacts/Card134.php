<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card134 extends AbstractCard
{

  // Cyrus Cylinder
  // - 3rd edition:
  //   - Choose any other top purple card on any player's board. Execute its non-demand dogma
  //     effects. Do not share them. Splay left a color on any player's board.
  // - 4th edition:
  //   - Splay left a color on any player's board.
  //   - Choose any other top purple card on any player's board. Self-execute it.

  public function hasPostExecutionLogic(): bool
  {
    return self::isFirstOrThirdEdition();
  }

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $isSplayInteraction = false;


    if ($isSplayInteraction) {
      return [
        'owner_from'  => 'any player',
        'choose_from' => 'board',
      ];
    } else {
      return [
        'owner_from'  => 'any player',
        'choose_from' => 'board',
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
    }

  }

  public function afterInteraction()
  {
    if (!self::isSplayInteraction()) {
      if (!(self::getNumChosen() === 1 && self::selfExecute(self::getLastSelectedCard())) && self::isFirstOrThirdEdition()) {
        // Even if a card should not be self-executed, we still need to go to the second interaction 
        self::setMaxSteps(2);
      }
    }
  }

  private function isSplayInteraction(): bool
  {
    if (self::isFourthEdition() && self::isFirstNonDemand()) {
      return true;
    }
    if (self::isFirstOrThirdEdition() && (self::isSecondInteraction() || self::getPostExecutionIndex() > 0)) {
      return true;
    }
    return false;
  }

}