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
    // TODO(4E): This is a bug. The player can deliberately choose a choice which will fail.
    if ($this->game->countCardsInLocation(self::getPlayerId(), 'safe') > 0) {
      self::setMaxSteps(1);
    } else {
      self::drawAndSafeguard(4);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choices' => [0, 1]];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'none',
      ];
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'board',
        'not_id'        => self::getAuxiliaryValue2(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $card = self::drawAndReveal(self::getLastSelectedAge() + 1);
      if (self::isRed($card) || self::isPurple($card)) {
        self::setAuxiliaryValue2(self::getLastSelectedId());
        self::setMaxSteps(3);
      } else {
        self::transferToHand($card);
      }
    } else if (self::isThirdInteraction()) {
      $revealedCard = self::getCards('revealed')[0];
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
      0 => [clienttranslate('Draw and reveal a card of value one higher than one your secrets'), []],
      1 => [clienttranslate('Draw and safeguard a ${age}'), 'age' => self::renderValue(4)],
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    $secrets = self::getCards('safe');
    if ($choice === 1) {
      self::drawAndSafeguard(4);
    } else if (count($secrets) === 1) {
      $secret = $secrets[0];
      $card = self::drawAndReveal($secret['faceup_age'] + 1);
      if (self::isRed($card) || self::isPurple($card)) {
        self::safeguard($card);
      } else {
        self::transferToHand($card);
      }
    } else {
      self::setMaxSteps(2);
    }
  }
}