<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card541 extends Card
{

  // Attic:
  //   - You may score or safeguard a card from your hand.
  //   - Return a card from your score pile.
  //   - Draw and score a card of value equal to a card in your score pile.

  public function initialExecution()
  {
    $scoreCards = $this->game->getCardsInScorePile(self::getPlayerId());
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(2);
    } else if (self::getEffectNumber() === 2 || count($scoreCards) >= 1) {
      self::setMaxSteps(1);
    } else if (self::getEffectNumber() === 3) {
      self::drawAndScore(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return self::getFirstInteractionOptions();
    } else if (self::getEffectNumber() === 2) {
      return [
        'location_from' => 'score',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'choose_value' => true,
        'age'          => self::getUniqueValues('score'),
      ];
    }
  }

  private function getFirstInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'         => true,
        'choose_yes_or_no' => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'location_to'   => self::getAuxiliaryValue() == 1 ? 'score' : 'safe',
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 3) {
      self::drawAndScore(self::getAuxiliaryValue());
    }
  }

  public function getPromptForListChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Score a card from hand'),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Safeguard a card from hand'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

}