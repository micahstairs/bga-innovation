<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card184 extends AbstractCard
{
  // The Communist Manifesto
  // - 3rd edition:
  //   - For each player in the game, draw and reveal a [7]. Transfer one of the drawn cards to
  //     each player's board. Execute the non-demand effects of your card. Do not share them.
  // - 4th edition:
  //   - For each player, draw and reveal a [7]. Transfer one of the drawn cards to each other
  //     player's board. Meld the last, and self-execute it.

  public function initialExecution()
  {
    foreach (self::getPlayerIds() as $playerId) {
      self::drawAndReveal(7);
    }
    $players = self::isFirstOrThirdEdition() ? $this->game->getAllActivePlayers() : $this->game->getOtherActivePlayers(self::getPlayerId());
    self::setAuxiliaryArray($players); // Track indexes (not IDs) of players which are selectable
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
      ];
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Track player that the card is transferring to
  }

  public function handleCardChoice(array $card)
  {
    if (self::getAuxiliaryValue() == self::getPlayerId()) {
      // In 3rd edition, track which card was melded by the launcher so it can be executed later
      self::setAuxiliaryValue2($card['id']);
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
    if (self::isFirstOrThirdEdition()) {
      self::selfExecute(self::getCard(self::getAuxiliaryValue2()));
    } else {
      self::selfExecute(self::meld(self::getRevealedCard()));
    }
  }

}