<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card548 extends AbstractCard
{

  // Safe Deposit Box:
  //   - You may choose to either draw and junk two [7], or exchange all cards in your score pile
  //     with all valued cards in the junk.

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

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw and junk two ${age}'), 'age' => self::renderValue(7)],
      2 => clienttranslate('Exchange all cards in your score pile with all valued junked cards'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      self::junk(self::draw(7));
      self::junk(self::draw(7));
    } else {
      $junkCards = self::getCards('junk');
      foreach (self::getCards('score') as $card) {
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