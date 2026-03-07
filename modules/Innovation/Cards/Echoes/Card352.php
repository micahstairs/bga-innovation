<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;

class Card352 extends AbstractCard
{

  // Watermill
  // - 3rd edition:
  //   - Tuck a card with a bonus from your hand. If you do, draw a card of value equal to that
  //     card's bonus. If the drawn card also has a bonus, you may return a card from your hand to
  //     repeat this dogma effect.
  // - 4th edition:
  //   - Draw a card of value equal to a bonus on your board, if you have one.
  //   - Tuck a card from your hand. If Watermill was foreseen, tuck all cards from the deck of
  //     value equal to the tucked card.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstOrThirdEdition()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->tuck()->withBonus()->fromYourHand()->revealingIfUnable();
      } else {
        return self::youMay()->return()->fromYourHand();
      }
    } else if (self::isFirstNonDemand()) {
      return self::youMust()->chooseValue(self::getBonuses());
    } else {
      return self::youMust()->tuck()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstOrThirdEdition()) {
      if (self::isFirstInteraction()) {
        $drawnCard = self::draw(self::getBonusIcon($card));
        if (self::hasBonusIcon($drawnCard)) {
          self::setMaxSteps(2);
        }
      } else {
        self::setNextStep(1);
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand() && self::wasForeseen()) {
      while ($topCard = $this->game->getDeckTopCard(self::getValue($card), CardTypes::BASE)) {
        self::tuck($topCard);
      }
    }
  }

  public function handleValueChoice(int $value)
  {
    self::draw($value);
  }

}