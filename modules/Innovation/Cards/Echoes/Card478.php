<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card478 extends AbstractCard
{

  // Deepfake
  //   - If it is your turn, transfer a top card from any board to your board, then super-execute
  //     a top card on your board other than Deepfake. If the transferred card is still a top card,
  //     transfer it to its original board.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::isPostExecution()) {
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
      return self::youMust()->fromAnyBoard()->toMyBoard()->build();
    } else {
      return self::youMust()->chooseCardFrom('board')->otherThan(CardIds::DEEPFAKE)->build();
    }
  }

  public function executeCardTransfer(array $card): bool
  {
    if (self::isFirstInteraction()) {
      // Intercept this card transfer so that we can tell where the card is coming from
      self::setAuxiliaryValue(self::getId($card));
      self::setAuxiliaryValue2($card['owner']);
      self::transferToBoard($card);
      return true;
    }
    return false;
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::superExecute($card);
    }
  }

}