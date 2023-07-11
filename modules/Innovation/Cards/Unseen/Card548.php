<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card548 extends Card
{

  // Safe Deposit Box:
  //   - You may choose to either draw and junk two [7], or exchange all cards in your score pile
  //     with all valued junked cards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass' => true,
      'choices'  => [1, 2],
    ];
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => [clienttranslate('Draw and junk two ${age}'), 'age' => $this->game->getAgeSquare(7)],
      2 => clienttranslate('Exchange all cards in your score pile with all valued junked cards'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      self::junk(self::draw(7));
      self::junk(self::draw(7));
    } else {
      $scoreCards = $this->game->getCardsInScorePile(self::getPlayerId());
      $junkCards = $this->game->getCardsInLocation(0, 'junk');
      foreach ($scoreCards as $card) {
        self::junk($card);
      }
      foreach ($junkCards as $card) {
        if (self::isValuedCard($card)) {
          self::transferToScorePile($card);
        }
      }
    }
  }

}