<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card147 extends AbstractCard
{

  // East India Company Charter
  // - 3rd edition:
  //   - Choose a value other than [5]. Return all cards of that value from all score piles. For
  //     each player that returned cards, draw and score a [5].
  // - 4th edition:
  //   - Choose a value other than [5]. Return all cards of that value from all score piles. For
  //     each score pile from which cards are returned, draw and score a [5]. If you do, junk all
  //     cards in the deck of the chosen value.


  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => [1, 2, 3, 4, 6, 7, 8, 9, 10, 11],
      ];
    } else {
      $valueToReturn = self::getAuxiliaryValue();
      $numAffectedScorePiles = 0;
      foreach (self::getPlayerIds() as $playerId) {
        foreach (self::getCards(Locations::SCORE, $playerId) as $card) {
          if ($card['age'] == $valueToReturn) {
            $numAffectedScorePiles++;
            break;
          }
        }
      }
      self::setAuxiliaryValue2($numAffectedScorePiles); // Track number of cards to draw and score
      return [
        'n'              => 'all',
        'owner_from'     => 'any player',
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'age'            => $valueToReturn,
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value); // Track value to return from all score piles
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $numCards = self::getAuxiliaryValue2();
      $numFivesScored = 0;
      for ($i = 0; $i < $numCards; $i++) {
        $card = self::drawAndScore(5);
        if (self::getValue($card) === 5) {
          $numFivesScored++;
        }
      }
      if (self::isFourthEdition() && $numFivesScored) {
        self::junkBaseDeck(self::getAuxiliaryValue());
      }
    }
  }
}