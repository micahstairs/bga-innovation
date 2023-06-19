<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card518 extends Card
{

  // Spanish Inquisition:
  //   - I demand you return all but the highest cards from your hand and all but the highest cards
  //     from your score pile!
  //   - If Spanish Inquisition is a top card on your board, return all red cards from your board.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->isDemand()) {
      $cardIds = array();
      $maxAgeInHand = $this->game->getMaxAgeInHand($state->getPlayerId());
      foreach ($this->game->getCardsInHand($state->getPlayerId()) as $card) {
        if ($card['age'] < $maxAgeInHand) {
          $cardIds[] = $card['id'];
        }
      }
      $maxAgeInScore = $this->game->getMaxAgeInScore($state->getPlayerId());
      foreach ($this->game->getCardsInScorePile($state->getPlayerId()) as $card) {
        if ($card['age'] < $maxAgeInScore) {
          $cardIds[] = $card['id'];
        }
      }
      if (count($cardIds) > 0) {
        $state->setMaxSteps(2);
        $this->game->setAuxiliaryArray($cardIds);
      }
    } else {
      $top_card = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game::RED);
      if ($top_card !== null && $top_card['id'] == 518) {
        $state->setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->isDemand()) {
      return [
        'n'                               => 'all',
        'location_from'                   => 'hand,score',
        'location_to'                     => 'deck',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'pile',
        'location_to'   => 'deck',
        'color'         => array($this->game::RED),
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isDemand() && $state->getNumChosen() > 0) {
        $topCard = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
        if ($topCard !== null) {
          $this->game->transferCardFromTo($topCard, $state->getLauncherId(), 'board');
        }
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose a color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color'),
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $this->game->setAuxiliaryValue($choice);
  }

}