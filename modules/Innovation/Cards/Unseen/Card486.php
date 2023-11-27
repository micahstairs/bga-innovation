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
      return [
        'choose_player' => true,
        'players'       => $this->game->getOtherActivePlayers(self::getPlayerId()),
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => 'board',
        'owner_to'      => self::getAuxiliaryValue(),
        'location_to'   => 'board',
        'with_icon'     => Icons::AUTHORITY,
      ];
    } else {
      return [
        'owner_from'    => self::getAuxiliaryValue(),
        'location_from' => 'board',
        'owner_to'      => self::getPlayerId(),
        'meld_keyword'  => true,
        'age'           => $this->game->getMinAgeOnBoardTopCardsWithoutIcon(self::getAuxiliaryValue(), Icons::AUTHORITY),
        'without_icon'  => Icons::AUTHORITY,
      ];
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
      self::setAuxiliaryValue($card['owner']);
    }
  }
}