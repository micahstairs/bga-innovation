<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Locations;

class Card136 extends AbstractCard
{

  // Charter of Liberties (3rd edition)
  //   - Tuck a card from your hand. If you do, splay left its color, then choose a splayed color
  //     on any player's board. Execute all of that color's top card's non-demand effects, without
  //     sharing.
  // Yata No Kagami (4th edition)
  //   - Reveal a card from your hand. If you do, splay left its color on your board, then choose a
  //     top card other than Yata No Kagami of that color on any board and self-execute it.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      if (self::isFirstOrThirdEdition()) {
        return self::youMust()->tuck()->fromYourHand()->build();
      } else {
        return self::youMust()->reveal()->fromYourHand()->build();
      }
    } else if (self::isFirstOrThirdEdition()) {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->currentlySplayed()->build();
    } else {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->withColor(self::getAuxiliaryValue())->otherThan(CardIds::YATA_NO_KAGAMI)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction() && self::getNumChosen() > 0) {
      if (self::isFourthEdition()) {
        self::transferToHand($card);
        self::setAuxiliaryValue(self::getColor($card)); // Track color to self-execute
      }
      self::splayLeft(self::getColor($card));
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction()) {
      self::selfExecute($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}