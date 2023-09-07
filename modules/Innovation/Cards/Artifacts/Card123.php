<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;

class Card123 extends Card
{

  // Ark of the Covenant
  // - 3rd edition:
  //   - Return a card from your hand. Transfer all cards of the same color from the boards of all
  //     players with no top Artifacts to your score pile. If Ark of the Covenant is a top card
  //     on any board, transfer it to your hand.
  // - 4th edition:
  //   - Return a card from your hand. Score all cards of the same color on the boards of all
  //     players with no top Artifacts.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'hand',
      'location_to'   => 'revealed,deck',
    ];
  }

  public function handleCardChoice(array $returnedCard)
  {
    $color = $returnedCard['color'];
    foreach (self::getPlayerIds() as $playerId) {
      $hasTopArtifact = false;
      foreach (self::getTopCards($playerId) as $card) {
        if ($card['type'] == CardTypes::ARTIFACTS) {
          $hasTopArtifact = true;
          break;
        }
      }
      if (!$hasTopArtifact) {
        foreach (array_reverse(self::getCardsKeyedByColor('board')[$color]) as $card) {
          if (self::isFirstOrThirdEdition()) {
            self::transferToScorePile($card);
          } else {
            self::score($card);
          }
        }
      }
    }
  }

  public function afterInteraction()
  {
    if ($card = $this->game->getIfTopCardOnBoard(CardIds::ARK_OF_THE_COVENANT)) {
      self::transferToHand($card);
    }
  }

}