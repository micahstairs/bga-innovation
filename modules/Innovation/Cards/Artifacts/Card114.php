<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card114 extends Card
{

  // Papyrus of Ani
  // - 3rd edition:
  //   - Return a purple card from your hand. If you do, draw and reveal a card of any type of
  //     value two higher. If the drawn card is purple, meld it and execute each of its non-demand
  //     dogma effects. Do not share them.
  // - 4th edition:
  //   - Return a purple card from your hand. If you do, draw and reveal a card of any type of
  //     value two higher. If the drawn card is purple, meld it and self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'revealed,deck',
        'color'         => [Colors::PURPLE],
      ];
    } else {
      return ['choose_type' => true];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setMaxSteps(2);
    self::setAuxiliaryValue($card['age'] + 2); // Track value to draw
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() === 0) {
      self::revealHand(); // Prove that there were no purple cards in hand
    }
  }

  public function handleTypeChoice(int $type)
  {
    $card = $this->game->executeDraw(self::getPlayerId(), self::getAuxiliaryValue(), 'revealed', /*bottom_to=*/false, $type);
    if (self::isPurple($card)) {
      self::selfExecute(self::meld($card));
    } else {
      self::transferToHand($card);
    }
  }

}