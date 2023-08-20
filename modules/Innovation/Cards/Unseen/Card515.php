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
      if (self::isFirstInteraction()) {
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

  public function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => [clienttranslate('Draw a ${age}'), 'age' => self::renderValue(4)],
      1 => [clienttranslate('Score a card from your hand'), []],
    ]);
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