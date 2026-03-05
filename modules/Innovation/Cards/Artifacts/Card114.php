<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card114 extends AbstractCard
{

  // Papyrus of Ani
  // - 3rd edition:
  //   - Return a purple card from your hand. If you do, draw and reveal a card of any type of
  //     value two higher. If the drawn card is purple, meld it and execute each of its non-demand
  //     dogma effects. Do not share them.
  // - 4th edition:
  //   - Return a purple card from your hand. If you do, draw and reveal a card from any set of
  //     value two higher. If the drawn card is purple, meld it and self-execute it.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->revealAndReturn()->withColor(Colors::PURPLE)->fromYourHand()->revealingIfUnable();
    } else {
      return self::youMust()->chooseType();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setMaxSteps(2);
    self::setAuxiliaryValue(self::getFaceupValue($card) + 2); // Track value to draw
  }

  public function handleTypeChoice(int $type)
  {
    $card = $this->game->executeDraw(self::getPlayerId(), self::getAuxiliaryValue(), Locations::REVEALED, /*bottom_to=*/ false, $type);
    if (self::isPurple($card)) {
      self::selfExecute(self::meld($card));
    } else {
      self::transferToHand($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isLauncher()) {
      return count(self::filterByColor(self::getCards(Locations::HAND), Colors::PURPLE)) > 0;
    } else {
      return self::hasCards(Locations::HAND);
    }
  }

}