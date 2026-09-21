<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

// TODO: Split this implementation into separate files for 3rd and 4th edition.
class Card353 extends AbstractCard
{

  // Pagoda
  // - 3rd edition:
  //   - Draw and reveal a [3]. If you have a card of matching color in your hand, tuck the card
  //     from your hand and meld the drawn card. Otherwise, foreshadow the drawn card.
  // - 4th edition:
  //   - Draw and foreshadow a [3]. You may tuck another card of matching color from your hand. If
  //     you do, meld the drawn card. If you do, and Pagoda was foreseen, meld all cards of that
  //     color from all other boards.

  public function initialExecution()
  {
    $card = self::isFirstOrThirdEdition() ? self::drawAndReveal(3) : self::drawAndForeshadow(3);
    self::setAuxiliaryValue(self::getId($card)); // Track the drawn card
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $color = self::getColor(self::getCard(self::getAuxiliaryValue()));
    if (self::isFirstInteraction()) {
      return self::youMay()->tuck()->fromYourHand()->withColor([$color]);
    } else {
      return self::youMust()->meld()->fromAnywhereInStack()->withColor([$color])->fromAnyOtherPlayer();
    }
  }

  public function afterInteraction()
  {
    $drawnCard = self::getCard(self::getAuxiliaryValue());
    if (self::getNumChosen() === 0) {
      if (self::isFirstOrThirdEdition()) {
        // Reveal hand to prove that there were no matching cards of the drawn card's color.
        // TODO: Use reveal_if_unable instead.
        self::revealHand();
        self::foreshadow($drawnCard);
      }
    } else {
      // Only reveal (in 4th edition) if a card was actually tucked
      if (self::isFourthEdition()) {
        // TODO(LATER): It would be a bit more natural if this was revealed before the card was
        // actually tucked (but after the player decided to tuck a card).
        $this->game->revealCardWithoutMoving(self::getPlayerId(), $drawnCard);
      }
      if (self::isSecondInteraction()) {
        self::meld($drawnCard);
      } else if (self::isFourthEdition() && self::wasForeseen()) {
        // Meld other boards' cards first so the drawn card from forecast can be melded last (on top).
        self::setMaxSteps(2);
      } else {
        self::meld($drawnCard);
      }
    }
  }

}