<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card486 extends Card
{

  // Dance:
  //   - Transfer a top card on your board with a [AUTHORITY] to the board of any other player. If
  //     you do, meld the lowest top card without a [AUTHORITY] from that player's board.

  public function initialExecution()
  {
    // Only bother making the player choose another player if they have a top card with any [AUTHORITY] icons
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, $this->game::AUTHORITY)) {
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
        'with_icon'     => $this->game::AUTHORITY,
      ];
    } else {
      return [
        'owner_from'    => self::getAuxiliaryValue(),
        'location_from' => 'board',
        'owner_to'      => self::getPlayerId(),
        'meld_keyword'  => true,
        'age'           => $this->game->getMinAgeOnBoardTopCardsWithoutIcon(self::getAuxiliaryValue(), $this->game::AUTHORITY),
        'without_icon'  => $this->game::AUTHORITY,
      ];
    }
  }

  public function handleSpecialChoice(int $playerId)
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