<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card515 extends AbstractCard
{

  // Quackery:
  //   - Choose to either score a card from your hand, or draw a [4].
  //   - Return exactly two cards in your hand. If you do, draw a card of value equal to the sum
  //     number of [HEALTH] and [CONCEPT] on the returned cards.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::countCards('hand') >= 2) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(0);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->choose([0, 1]);
      } else {
        return self::youMust()->score()->fromYourHand();
      }
    } else {
      return self::youMust()->revealAndReturn()->exactly(2)->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    $sum = self::getAuxiliaryValue();
    $sum += $this->game->countIconsOnCard($card, Icons::HEALTH);
    $sum += $this->game->countIconsOnCard($card, Icons::CONCEPT);
    self::setAuxiliaryValue($sum);
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      self::draw(self::getAuxiliaryValue());
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => [clienttranslate('Draw a ${age}'), 'age' => self::renderValue(4)],
      1 => [clienttranslate('Score a card from your hand')],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 0) {
      self::draw(4);
    } else {
      self::setMaxSteps(2);
    }
  }

}