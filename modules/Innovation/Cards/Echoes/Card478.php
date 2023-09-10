<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;

class Card478 extends Card
{

  // Deepfake
  //   - If it is your turn, transfer a top card from any board to your board, then fully execute
  //     a top card on your board other than Deepfake. If the transferred card is still a top card,
  //     transfer it to its original board.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::getPostExecutionIndex() > 0) {
      $card = self::getCard(self::getAuxiliaryValue());
      if ($this->game->isTopBoardCard($card)) {
        self::transferToBoard($card, self::getAuxiliaryValue2());
      }
    } else if (self::isTheirTurn()) {
      self::setAuxiliaryValue(-1);
      self::setAuxiliaryValue2(-1);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'board',
        'location_to'   => 'board',
      ];
    } else {
      return [
        'choose_from' => 'board',
        'not_id'      => CardIds::DEEPFAKE,
      ];
    }
  }

  public function executeCardTransfer(array $card): bool
  {
    if (self::isFirstInteraction()) {
      // Intercept this card transfer so that we can tell where the card is coming from
      self::setAuxiliaryValue($card['id']);
      self::setAuxiliaryValue2($card['owner']);
      self::transferToBoard($card);
      return true;
    }
    return false;
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::fullyExecute($card);
    }
  }

}