<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card516 extends Card
{

  // The Prophecies:
  //   - Choose to either draw and safeguard a [4], or draw and reveal a card of 
  //     value one higher than one your secrets. If you reveal a red or purple card, 
  //     meld one of your other secrets. If you do, safeguard the drawn card.

  public function initialExecution(ExecutionState $state)
  {
    if ($this->game->countCardsInLocation($state->getPlayerId(), 'safe') > 0) {
      $state->setMaxSteps(1);
    } else {
      // "draw and safeguard a [4]"
      $this->game->executeDrawAndSafeguard($state->getPlayerId(), 4);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    
      if ($state->getCurrentStep() == 1) {
        return ['choose_yes_or_no' => true];
      } else if ($state->getCurrentStep() == 2) {
        // "draw and reveal a card of value one higher than one your secrets."
        // select a secret
        return [
          'location_from' => 'safe',
          'location_to'   => 'none',
        ];
      } else {
          // "meld one of your other secrets."
        return [
          'location_from' => 'safe',
          'location_to'   => 'board',
          'not_id' => $this->game->getAuxiliaryValue2(),
        ];
      }
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getCurrentStep() == 2) {
      $card = $this->game->executeDrawAndReveal($state->getPlayerId(), $this->game->get('age_last_selected') + 1);
      if ($card['color'] == 1 || $card['color'] == 4) {
        $state->setMaxSteps(3); // need to choose a card to meld
        
        $this->game->setAuxiliaryValue2($this->game->get('id_last_selected'));
      } else {
        $this->game->transferCardFromTo($card, $state->getPlayerId(), 'hand'); // otherwise put in hand
      }
    } else if ($state->getCurrentStep() == 3) {
        $cards_in_reveal = $this->game->getCardsInLocation($state->getPlayerId(), 'reveal');
        if ($state->getNumChosen() > 0) {
            $this->game->safeguardCard($cards_in_reveal[0], $state->getPlayerId());
        }
    }

  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    $ageToDraw = $this->game->getAgeToDrawIn($state->getPlayerId(), 4);
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $ageToDraw <= $this->game->getMaxAge() ? clienttranslate('Draw and safeguard a ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($ageToDraw),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Draw and reveal a card of value one higher than one your secrets.'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $safe_cards = $this->game->getCardsInLocation($state->getPlayerId(), 'safe');
    if ($choice === 1) {
      $this->game->executeDrawAndSafeguard($state->getPlayerId(), 4);
    } elseif(count($safe_cards) == 1) {
      $safe_card = $safe_cards[0];
      $card = $this->game->executeDrawAndReveal($state->getPlayerId(), $safe_card['age'] + 1);
      if ($card['color'] == 1 || $card['color'] == 4) {
        $this->game->safeguardCard($card, $state->getPlayerId());
      } else {
        $this->game->transferCardFromTo($card, $state->getPlayerId(), 'hand'); // otherwise put in hand
      }
    } else {
      $state->setMaxSteps(2);
    }
  }
}