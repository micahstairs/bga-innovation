<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card515 extends Card
{

  // Quackery:
  //   - Choose to either score a card from your hand, or draw a [4].
  //   - Return exactly two cards in your hand. If you do, draw a card of value equal to the sum
  //     number of [HEALTH] and [CONCEPT] on the returned cards.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->getEffectNumber() == 1) {
      $state->setMaxSteps(1);
    } else if ($this->game->countCardsInHand($state->getPlayerId()) >= 2) {
      $state->setMaxSteps(1);
      $this->game->setAuxiliaryValue(0);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getEffectNumber() == 1) {
      if ($state->getCurrentStep() == 1) {
        return ['choose_yes_or_no' => true];
      } else {
        return [
          'location_from' => 'hand',
          'score_keyword' => true,
        ];
      }
    } else {
      return [
        'n'             => 2,
        'location_from' => 'hand',
        'location_to'   => 'revealed,deck',
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    $card = $this->game->getCardInfo($cardId);
    $sum = $this->game->getAuxiliaryValue();
    $sum += $this->game->countIconsOnCard($card, $this->game::HEALTH);
    $sum += $this->game->countIconsOnCard($card, $this->game::CONCEPT);
    $this->game->setAuxiliaryValue($sum);
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getEffectNumber() == 2) {
      self::draw($this->game->getAuxiliaryValue());
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
          'text'  => clienttranslate('Score a card from your hand')
        ],
        [
          'value' => 0,
          'text'  => $ageToDraw <= $this->game->getMaxAge() ? clienttranslate('Draw a ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($ageToDraw)
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    if ($choice === 0) {
      self::draw(4);
    } else {
      $state->setMaxSteps(2);
    }
  }

}