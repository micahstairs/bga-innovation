<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card521 extends Card
{

  // April Fool's Day:
  //   - Transfer the highest cards from your hand and score pile together to the 
  //     board of the player on your right. If you don't, claim the Folklore achievement.
  //   - Splay your yellow cards right, and unsplay your purple cards, or vice versa.
  public function initialExecution(ExecutionState $state)
  {
    if ($state->getEffectNumber() == 1) {
      $cardIds = array_merge(self::getCardsIdOfMaxAgeInLocation('hand'), self::getCardsIdOfMaxAgeInLocation('score'));
      if (count($cardIds) == 0) {
        $this->game->claimSpecialAchievement($state->getPlayerId(), 598);
      } else {
        $state->setMaxSteps(1);
        $this->game->setAuxiliaryArray($cardIds);
      }
    } else {
      $state->setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getEffectNumber() == 1) {
      return [
        'n'                               => 'all',
        'location_from'                   => 'hand,score',
        'owner_to'                        => $this->game->getActivePlayerIdOnRightOfActingPlayer(),
        'location_to'                     => 'board',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return ['choose_yes_or_no' => true];
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Splay yellow right and unsplay purple'),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Splay purple right and unsplay yellow'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    if ($choice === 1) {
      self::splayRight($this->game::YELLOW);
      self::unsplay($this->game::PURPLE);
    } else {
      self::splayRight($this->game::PURPLE);
      self::unsplay($this->game::YELLOW);
    }
  }

  private function getCardsIdOfMaxAgeInLocation(string $location): array
  {
    $playerId = $this->state->getPlayerId();
    $cardIds = [];
    $maxAge = $this->game->getMaxAgeInHand($playerId);
    if ($maxAge > 0) {
      $maxAgeCards = $this->game->getCardsInLocationKeyedByAge($playerId, $location)[$maxAge];
      foreach ($maxAgeCards as $card) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}