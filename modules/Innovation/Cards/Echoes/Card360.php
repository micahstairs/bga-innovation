<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card360 extends AbstractCard
{

  // Homing Pigeons
  // - 3rd edition:
  //   - I DEMAND you return two cards from your score pile whose values each match at least one card in my hand!
  //   - You may splay your red or green cards left.
  // - 4th edition:
  //   - I DEMAND you return two cards from your score pile whose values each match a card in my hand!
  //   - You may splay your red or green cards left. If Homing Pigeons was foreseen, splay all your
  //     colors left.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardIds = [];
      $handCounts = self::countCardsKeyedByValue(Locations::HAND, self::getLauncherId());
      foreach (self::getCards(Locations::SCORE) as $card) {
        if ($handCounts[self::getValue($card)] > 0) {
          $cardIds[] = self::getId($card);
        }
      }
      self::setMaxSteps(1);
      self::setAuxiliaryArray($cardIds);
    } else {
      self::setMaxSteps(1);
    }

  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->return()->exactly(2)->onlyCardsInAuxiliaryArray()->fromYourScore();
    } else {
      return self::youMay()->splayLeft([Colors::RED, Colors::GREEN]);
    }
  }

  public function afterInteraction(): void
  {
    if (self::isNonDemand() && self::wasForeseen()) {
      // TODO(4E): Does afterInteraction() get called if there are no colors which can be splayed left?
      foreach (Colors::ALL as $color) {
        self::splayLeft($color);
      }
    }
  }

}