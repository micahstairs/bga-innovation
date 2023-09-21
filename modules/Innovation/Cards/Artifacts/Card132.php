<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;

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

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      return [
        'location_from'  => 'board',
        'return_keyword' => true,
        'without_icon'   => Icons::AUTHORITY,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'score_keyword' => true,
        'without_icon'  => Icons::AUTHORITY,
      ];
    }
  }

  public function afterInteraction() {
    if (self::isFirstNonDemand()) {
      if (self::getNumChosen() > 0) {
        if (self::isFourthEdition()) {
          self::junkBaseDeck(self::getLastSelectedAge());
        }
      } else {
        self::revealHand(); // Prove that no card could be scored
        if (self::isFourthEdition()) {
          self::tuck(self::getCard(CardIds::TERRACOTTA_ARMY));
        }
      }
    }
  }

}