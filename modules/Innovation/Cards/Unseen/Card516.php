<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card516 extends Card
{

  // The Prophecies:
  //   - Choose to either draw and safeguard a [4], or draw and reveal a card of value one higher
  //     than one of your secrets. If you reveal a red or purple card, meld one of your other secrets.
  //     If you do, safeguard the drawn card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choices' => [0, 1]];
    } else if (self::isSecondInteraction()) {
      return ['choose_from' => 'safe'];
    } else {
      return [
        'location_from' => 'safe',
        'meld_keyword'  => 'true',
        'not_id'        => self::getAuxiliaryValue2(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $card = self::drawAndReveal(self::getLastSelectedAge() + 1);
      if (self::isRed($card) || self::isPurple($card)) {
        self::setAuxiliaryValue2(self::getLastSelectedId()); // Track chosen secret
        self::setMaxSteps(3);
      } else {
        self::transferToHand($card);
      }
    } else if (self::isThirdInteraction()) {
      $revealedCard = self::getRevealedCard();
      if (self::getNumChosen() > 0) {
        self::safeguard($revealedCard);
        // Put all revealed cards in hand if they can't fit in the safe
        foreach (self::getCards('revealed') as $card) {
          self::transferToHand($card);
        }
      } else {
        self::transferToHand($revealedCard);
      }
    }

  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => [clienttranslate('Draw and reveal a card of value one higher than one your secrets')],
      1 => [clienttranslate('Draw and safeguard a ${age}'), 'age' => self::renderValue(4)],
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      self::drawAndSafeguard(4);
    } else {
      self::setMaxSteps(2);
    }
  }
}