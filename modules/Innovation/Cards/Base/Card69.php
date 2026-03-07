<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card69 extends AbstractCard
{
  // Bicycle:
  // - 3rd edition:
  //   - You may exchange all the cards in your hand with all the cards in your score pile. If you
  //     exchange one, you must exchange them all.
  // - 4th edition:
  //   - You may exchange all cards in your hand with all cards in your score pile.

  public function initialExecution()
  {
    if (self::hasCards(Locations::HAND) || self::hasCards(Locations::SCORE)) {
      self::setMaxSteps(1);
    } else {
      self::notifyPlayer('${You} have no cards to exchange.');
      self::notifyOthers('${player_name} has no cards to exchange.');
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->choose([1]);
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Exchange hand and score pile')],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    $cardsInHand = self::getCards(Locations::HAND);
    $cardsInScore = self::getCards(Locations::SCORE);
    foreach ($cardsInHand as $card) {
      self::transferToScorePile($card);
    }
    foreach ($cardsInScore as $card) {
      self::transferToHand($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::hasCards(Locations::SCORE);
  }

}