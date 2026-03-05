<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card216 extends AbstractCard
{

  // Complex Numbers
  //   - You may reveal a card from your hand having exactly the same icons, in type and number, as a top card on your board. If you do, claim an achievement of matching value, ignoring eligibility.

  public function initialExecution()
  {
    if (self::hasCards(Locations::HAND)) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryArray(self::getEligibleCardIds());
      return self::youMay()->reveal()->onlyCardsInAuxiliaryArray()->fromYourHand()->withoutAutoselection();
    } else {
      return self::youMust()->achieve()->value(self::getLastSelectedAge());
    }
  }

  public function handleCardChoice(array $card)
  {
    self::transferToHand($card);
    self::setMaxSteps(2);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::getEligibleCardIds()) > 0;
  }

  private function getEligibleCardIds(): array
  {
    $cardIds = [];
    $topCards = self::getTopCards();
    // NOTE: Bonus icons and other special city icons are counted as per https://boardgamegeek.com/thread/1872362/article/40784224.
    foreach (self::getCards(Locations::HAND) as $card) {
      $eligible = false;
      // TODO(LATER): See if we can optimize this.
      foreach ($topCards as $topCard) {
        $matchFound = true;
        if ($topCard !== null) {
          // search icons are considered different than basic icons so they need to be handled separately
          if ($card['spot_6'] !== null && $topCard['spot_6'] !== null && $card['spot_6'] != $topCard['spot_6']) {
            $matchFound = false; // If the search icons don't match
            break;
          }
          for ($icon = 1; $icon <= 13; $icon++) {
            // Echo effects are not considered icons, so they are skipped
            if ($icon == 10) {
              continue;
            }
            if (self::countExactIconsOnCard($card, $icon) != self::countExactIconsOnCard($topCard, $icon)) {
              $matchFound = false; // If any icon counts mismatch, then the card isn't eligible
              break;
            }
          }
          for ($icon = 101; $icon <= 112; $icon++) { // count bonus icons
            if (self::countExactIconsOnCard($card, $icon) != self::countExactIconsOnCard($topCard, $icon)) {
              $matchFound = false; // If any icon counts mismatch, then the card isn't eligible
              break;
            }
          }
          if ($matchFound) {
            $eligible = true;
          }
        }
      }
      if ($eligible) {
        $cardIds[] = self::getId($card);
      }
    }

    return $cardIds;
  }

}