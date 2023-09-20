<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card418 extends Card
{

  // Jet
  // - 3rd edition
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the color I melded due to Jet's echo effect!
  // - 4th edition
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the last color I melded due to Jet's echo effect during this
  //     action! Junk all available achievements of values equal to the melded card and the returned card!
  //   - Draw and foreshadow a [10].

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition() && self::isLauncher() && !$this->game->isExecutingAgainDueToEndorsedAction()) {
        self::setAuxiliaryArray([]); // Track colors melded by launcher due to echo effect
      }
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(self::isFirstOrThirdEdition() ? 1 : 2);
    } else {
      self::drawAndForeshadow(10);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else {
      if (self::isFirstOrThirdEdition()) {
        $colors = self::getAuxiliaryArray();
      } else {
        $colors = [];
        $faceupValues = [];
        // NOTE: A loop is used here for convenience, but this array will contain at most one element.
        foreach (self::getActionScopedAuxiliaryArray(self::getPlayerId()) as $cardId) {
          $card = self::getCard($cardId);
          $colors[] = $card['color'];
          $faceupValues[] = $card['faceup_value'];
        }
        self::setActionScopedAuxiliaryArray($faceupValues, self::getPlayerId()); // Repurpose array to store the values to junk
      }
      return [
        'location_from'  => 'board',
        'return_keyword' => true,
        'color'          => $colors,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isEcho() && self::isLauncher()) {
      if (self::isFirstOrThirdEdition()) {
        self::addToAuxiliaryArray($card['color']);
      } else {
        self::setActionScopedAuxiliaryArray([$card['id']], self::getPlayerId()); // Track melded card
      }
    } else if (self::isDemand() && self::isFourthEdition()) {
      self::addToActionScopedAuxiliaryArray(self::getPlayerId(), $card['age']); // Track value of returned card
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isDemand()) {
      foreach (self::getActionScopedAuxiliaryArray(self::getPlayerId()) as $value) {
        foreach (self::getCardsKeyedByValue(Locations::AVAILABLE_ACHIEVEMENTS)[$value] as $card) {
          self::junk($card);
        }
      }
    }
  }

}