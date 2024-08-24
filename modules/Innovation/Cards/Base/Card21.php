<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card21 extends AbstractCard
{
  // Canal Building:
  // - 3rd edition:
  //   - You may exchange all the highest cards in your hand with all the highest cards in your
  //     score pile.
  // - 4th edition:
  //   - You may choose to either exchange all the highest cards in your hand with all the highest
  //     cards in your score pile, or junk all cards in the [3] deck.

  public function initialExecution()
  {
    if (self::nonDemandsMightBeEffective()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $choices = self::isFourthEdition() ? [1, 2] : [1];
    return self::youMay()->choose($choices)->build();
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Exchange highest cards in your hand with the highest cards in your score pile'), 'age' => self::renderValue(self::getMaxValueInLocation('score') + 1)],
      2 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(3, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      $highestCardsInHand = self::getHighestCards(Locations::HAND);
      $highestCardsInScore = self::getHighestCards(Locations::SCORE);
      foreach ($highestCardsInHand as $card) {
        self::transferToScorePile($card);
      }
      foreach ($highestCardsInScore as $card) {
        self::transferToHand($card);
      }
    } else {
      self::junkBaseDeck(3);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::hasCards(Locations::SCORE) || (self::isFourthEdition() && self::getBaseDeckCount(3) > 0);
  }

}