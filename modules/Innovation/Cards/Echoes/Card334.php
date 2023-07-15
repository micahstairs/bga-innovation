<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card334 extends Card
{

  // Candles
  // - 3rd edition:
  //   - ECHO: If every other player has a higher score than you, draw a [3].
  //   - I DEMAND you transfer a card with a [AUTHORITY] from your hand to my hand! If you do, draw a [1]!
  // - 4th edition:
  //   - ECHO: If no player has fewer points than you, draw a [3].
  //   - I DEMAND you transfer a card with a [AUTHORITY] or [CONCEPT] from your hand to my hand! If you do, draw a [1]!

  public function initialExecution()
  {
    if (self::isEcho()) {
      $playerScore = self::getPlayerScore();
      $minScore = true;
      foreach ($this->game->getOtherActivePlayers(self::getPlayerId()) as $otherPlayerId) {
        $otherPlayerScore = self::getPlayerScore($otherPlayerId);
        if (self::isFirstOrThirdEdition() && $otherPlayerScore <= $playerScore) {
          $minScore = false;
        } else if (self::isFourthEdition() && $otherPlayerScore < $playerScore) {
          $minScore = false;
        }
      }
      if ($minScore) {
        self::draw(3);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'hand',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'hand',
        'with_icon'     => $this->game::AUTHORITY,
      ];
    } else {
      self::setAuxiliaryArray(self::getCardIdsInHandWithAuthorityOrConceptIcon());
      return [
        'owner_from'                      => self::getPlayerId(),
        'location_from'                   => 'hand',
        'owner_to'                        => self::getLauncherId(),
        'location_to'                     => 'hand',
        'with_icon'                       => $this->game::AUTHORITY,
        'card_ids_are_in_auxiliary_array' => true,
        'enable_autoselection'            => false,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::draw(1);
  }

  public function afterInteraction(): void
  {
    if (self::getNumChosen() === 0) {
      $this->game->revealHand(self::getPlayerId());
    }
  }

  private function getCardIdsInHandWithAuthorityOrConceptIcon(): array
  {
    $cardIds = [];
    foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
      if (self::hasIcon($card, $this->game::AUTHORITY) || self::hasIcon($card, $this->game::CONCEPT)) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}