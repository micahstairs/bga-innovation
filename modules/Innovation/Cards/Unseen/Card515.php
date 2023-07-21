<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card515 extends Card
{

  // Quackery:
  //   - Choose to either score a card from your hand, or draw a [4].
  //   - Return exactly two cards in your hand. If you do, draw a card of value equal to the sum
  //     number of [HEALTH] and [CONCEPT] on the returned cards.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else if ($this->game->countCardsInHand(self::getPlayerId()) >= 2) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(0);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      if (self::getCurrentStep() === 1) {
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

  public function handleCardChoice(array $card)
  {
    $sum = self::getAuxiliaryValue();
    $sum += $this->game->countIconsOnCard($card, $this->game::HEALTH);
    $sum += $this->game->countIconsOnCard($card, $this->game::CONCEPT);
    self::setAuxiliaryValue($sum);
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 2) {
      self::draw(self::getAuxiliaryValue());
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    $ageToDraw = $this->game->getAgeToDrawIn(self::getPlayerId(), 4);
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

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 0) {
      self::draw(4);
    } else {
      self::setMaxSteps(2);
    }
  }

}