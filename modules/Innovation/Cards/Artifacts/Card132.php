<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card132 extends AbstractCard
{

  // Terracotta Army
  // - 3rd edition:
  //   - I COMPEL you to return a top card with no [AUTHORITY] from your board!
  //   - Score a card from your hand with no [AUTHORITY].
  // - 4th edition:
  //   - I COMPEL you to return a top card with no [AUTHORITY] from your board!
  //   - Score a card from your hand with no [AUTHORITY]. If you do, junk all cards in the deck of
  //     value equal to the scored card. Otherwise, tuck Terracotta Army.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isCompel()) {
      return self::youMust()->return()->fromYourBoard()->withoutIcon(Icons::AUTHORITY);
    } else {
      return self::youMust()->score()->fromYourHand()->withoutIcon(Icons::AUTHORITY)->revealingIfUnable();
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isFirstNonDemand()) {
      if (self::getNumChosen() > 0) {
        self::junkBaseDeck(self::getLastSelectedAge());
      } else {
        self::tuck(self::getCard(CardIds::TERRACOTTA_ARMY));
      }
    }
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (!self::hasIcon($card, Icons::AUTHORITY)) {
        return true;
      }
    }
    return false;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    // If 4th edition, we always do something (for simplicity, let's not check for the situation when the tuck is ineffective)
    if (self::isFourthEdition()) {
      return true;
    }

    return self::hasCards(Locations::HAND);
  }

}