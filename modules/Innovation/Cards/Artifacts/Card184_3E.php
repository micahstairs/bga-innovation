<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card184_3E extends AbstractCard
{
  // The Communist Manifesto (3rd edition):
  //   - For each player in the game, draw and reveal a [7]. Transfer one of the drawn cards to
  //     each player's board. Execute the non-demand effects of your card. Do not share them.

  public function initialExecution()
  {
    foreach (self::getPlayerIds() as $playerId) {
      self::drawAndReveal(7);
    }
    self::setAuxiliaryArray($this->game->getAllActivePlayers()); // Track indexes (not IDs) of players which are selectable
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_player' => true,
        'players'       => self::getAuxiliaryArray(),
      ];
    } else {
      return [
        'location_from' => Locations::REVEALED,
        'location_to'   => Locations::BOARD,
        'owner_to'      => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Track player that the card is transferring to
  }

  public function handleCardChoice(array $card)
  {
    if (self::getAuxiliaryValue() == self::getLauncherId()) {
      self::setAuxiliaryValue2($card['id']); // Track which card was melded by the launcher so it can be executed later
    }

    if (count(self::getAuxiliaryArray()) > 1) {
      // Remove the selected player from the list of options
      $playerId = $this->game->playerIdToPlayerIndex(self::getAuxiliaryValue());
      self::removeFromAuxiliaryArray($playerId);
      self::setNextStep(1);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::selfExecute(self::getCard(self::getAuxiliaryValue2()));
    }
  }

}