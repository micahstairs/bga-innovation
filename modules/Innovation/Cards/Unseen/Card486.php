<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card486 extends AbstractCard
{

  // Dance:
  //   - Transfer a top card on your board with [AUTHORITY] to the board of any other player. If
  //     you do, meld the lowest top card without [AUTHORITY] from that player's board.

  public function initialExecution()
  {
    // Only bother making the player choose another player if they have a top card with any [AUTHORITY] icons
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        self::setMaxSteps(2);
        break;
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getOtherPlayers())->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->withIcon(Icons::AUTHORITY)->fromYourBoard()->toBoard(self::getAuxiliaryValue())->build();
    } else {
      $value = $this->game->getMinAgeOnBoardTopCardsWithoutIcon(self::getAuxiliaryValue(), Icons::AUTHORITY);
      return self::youMust()->meld()->value($value)->withoutIcon(Icons::AUTHORITY)->fromBoard(self::getAuxiliaryValue())->toYours()->build();
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::setMaxSteps(3);
      self::setAuxiliaryValue(self::getOwner($card));
    }
  }
}