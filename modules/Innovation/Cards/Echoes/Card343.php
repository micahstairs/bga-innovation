<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Directions;

class Card343 extends AbstractCard
{

  // Flute
  // - 3rd edition:
  //   - ECHO: You may splay one color of your cards left.
  //   - I DEMAND you return a card with a bonus from your hand!
  //   - Draw and reveal a [1]. If it has a bonus, draw a [1].
  // - 4th edition:
  //   - ECHO: You may splay one color of your cards left.
  //   - I DEMAND you return an expansion card from your hand!
  //   - Draw and reveal an Echoes [1]. If it has a bonus, draw a [1].

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      if (self::isFirstOrThirdEdition()) {
        $card = self::drawAndReveal(1);
      } else {
        $card = self::drawAndRevealType(1, CardTypes::ECHOES);
      }
      self::transferToHand($card);
      if (self::hasBonusIcon($card)) {
        self::draw(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
      ];
    } else if (self::isFirstOrThirdEdition()) {
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
        'with_bonus'     => true,
      ];
    } else {
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
        'type'           => CardTypes::getAllTypesOtherThan(CardTypes::BASE),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::getNumChosen() === 0 && self::isFirstOrThirdEdition()) {
      // Prove that the player has no bonuses in their hand
      self::revealHand();
    }
  }

}