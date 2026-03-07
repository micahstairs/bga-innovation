<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card368 extends AbstractCard
{

  // Shuriken
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-red card with a [AUTHORITY] or [CONCEPT] from your board
  //     to my board! If you do, draw a [4]!
  //   - You may splay your purple cards right.
  // - 4th edition:
  //   - I DEMAND you transfer two non-red top cards with [AUTHORITY] or [AVATAR] of different
  //     colors from your board to my board! If you do, and Shuriken was foreseen, transfer them
  //     to my achievements!
  //   - You may splay your purple cards right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition()) {
        return self::youMust()
          ->non(Colors::RED)
          ->withIcons([Icons::AUTHORITY, Icons::CONCEPT])
          ->fromYourBoard()
          ->toMyBoard();
      } else {
        self::setAuxiliaryArray([]); // Tracks cards transferred
        return self::youMust()
          ->exactly(2)
          ->non(Colors::RED)
          ->withIcons([Icons::AUTHORITY, Icons::AVATAR])
          ->fromYourBoard()
          ->toMyBoard();
      }
    } else {
      return self::youMay()->splayRight(Colors::PURPLE);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isDemand() && self::isFourthEdition()) {
      self::addToAuxiliaryArray(self::getId($card));
    }
  }


  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition() && self::getNumChosen() === 1) {
        self::draw(4);
      } else if (self::wasForeseen() && self::getNumChosen() === 2) {
        foreach (self::getAuxiliaryArray() as $cardId) {
          self::transferToAchievements(self::getCard($cardId), self::getLauncherId());
        }
      }
    }
  }

}