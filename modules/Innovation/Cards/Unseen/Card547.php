<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card547 extends AbstractCard
{

  // Camouflage:
  //   - Choose to either junk exactly two top cards of different colors and equal value on your
  //     board, then safeguard them, or score exactly two of your secrets of equal value.
  //   - Draw a [7] for each special achievement you have.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
        if (self::isSpecialAchievement($card)) {
          self::draw(7);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choose([0, 1])->build();
    } else if (self::isSecondInteraction()) {
      if (self::getAuxiliaryValue() === 1) {
        $topCards = self::getTopCards();
        $colors = self::getColorsMatchingValues($topCards, self::getRepeatedValues($topCards));
        return self::youMust()->withColor($colors)->fromYourBoard()->toLocation(Locations::JUNK_THEN_SAFEGUARD)->build();
      } else {
        self::setAuxiliaryArray(self::getCardIdsWithDuplicateValuesInLocation('safe'));
        return self::youMust()->score()->onlyCardsInAuxiliaryArray()->fromYourSafe()->build();
      }
    } else {
      if (self::getAuxiliaryValue() === 1) {
        return self::youMust()->non(self::getLastSelectedColor())->value(self::getLastSelectedAge())->fromYourBoard()->toLocation(Locations::JUNK_THEN_SAFEGUARD)->build();
      } else {
        return self::youMust()->score()->value(self::getLastSelectedAge())->fromYourSafe()->build();
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Score two of your secrets'),
      1 => clienttranslate('Junk and safeguard two of your top cards'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 0) {
      $cardIds = self::getCardIdsWithDuplicateValuesInLocation('safe');
      if ($cardIds) {
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(3);
      }
    } else {
      if (self::getRepeatedValues(self::getTopCards())) {
        self::setMaxSteps(3);
      }
    }
    self::setAuxiliaryValue($choice);
  }

  private function getCardIdsWithDuplicateValuesInLocation(string $location): array
  {
    $cardIds = [];
    $cardsByAge = self::getCardsKeyedByValue($location);
    for ($age = 1; $age <= 11; $age++) {
      if (count($cardsByAge[$age]) >= 2) {
        foreach ($cardsByAge[$age] as $card) {
          $cardIds[] = self::getId($card);
        }
      }
    }
    return $cardIds;
  }

}