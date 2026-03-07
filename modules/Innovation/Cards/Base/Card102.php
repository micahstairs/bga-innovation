<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card102 extends AbstractCard
{
  // Stem Cells:
  // - 3rd edition:
  //   - You may score all cards from your hand. If you score one, you must score them all.
  // - 4th edition:
  //   - You may score all cards from your hand. If you score one, you must score them all.
  //   - Draw an [11].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::hasCards(Locations::HAND)) {
        self::setMaxSteps(1);
      } else {
        self::notifyPlayer(clienttranslate('${You} have no cards in your hand to score.'));
        self::notifyOthers(clienttranslate('${player_name} has no cards in their hand to score.'));
      }
    } else if (self::isSecondNonDemand()) {
      self::draw(11);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->choose([1]);
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Score all cards in your hand')],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    foreach (self::getCards(Locations::HAND) as $card) {
      self::score($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::isFourthEdition();
  }

}