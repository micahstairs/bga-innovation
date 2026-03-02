<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card484 extends AbstractCard
{

  // Maze:
  //   - I DEMAND you score a card from your hand of matching color for each card in my hand! If
  //     you don't, and I have a card in my hand, exchange all cards in your hand with all cards in
  //     my score pile!

  public function initialExecution()
  {
    $colorCounts = [0, 0, 0, 0, 0];
    $cards = self::getCards(Locations::HAND, self::getLauncherId());
    foreach ($cards as $card) {
      $colorCounts[self::getColor($card)]++;
    }
    if ($cards) {
      self::revealHand(self::getLauncherId());
    }
    self::setActionScopedAuxiliaryArray($colorCounts);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $cardIds = [];
    $colorCounts = self::getActionScopedAuxiliaryArray();
    foreach (self::getCards(Locations::HAND) as $card) {
      if ($colorCounts[self::getColor($card)] > 0) {
        $cardIds[] = self::getId($card);
      }
    }
    self::setAuxiliaryArray($cardIds);
    // Automating the selection can sometimes reveal hidden info
    return self::youMust()->score()->onlyCardsInAuxiliaryArray()->fromYourHand()->revealingIfUnable()->withoutAutoselection()->build();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      $launcherCardsInHand = self::getCards(Locations::HAND, self::getLauncherId());
      $cardsInHand = self::getCards(Locations::HAND);
      if (array_sum(self::getActionScopedAuxiliaryArray()) > 0 && count($launcherCardsInHand) > 0) {
        $cardsInScorePile = self::getCards(Locations::SCORE, self::getLauncherId());
        foreach ($cardsInHand as $card) {
          self::transferToScorePile($card, self::getLauncherId());
        }
        foreach ($cardsInScorePile as $card) {
          self::transferToHand($card);
        }
      }
    } else {
      $color = self::getLastSelectedColor();
      $colorCounts = self::getActionScopedAuxiliaryArray();
      $colorCounts[$color]--;
      self::setActionScopedAuxiliaryArray($colorCounts);
      self::setNextStep(1);
    }
  }

  private function getSelectableCardIds($playerId)
  {
    $cardIds = [];
    $colorCounts = self::getActionScopedAuxiliaryArray();
    foreach (self::getCards(Locations::HAND, $playerId) as $card) {
      if ($colorCounts[self::getColor($card)] > 0) {
        $cardIds[] = self::getId($card);
      }
    }
    return $cardIds;
  }
}