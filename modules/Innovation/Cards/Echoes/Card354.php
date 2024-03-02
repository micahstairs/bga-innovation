<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card354 extends AbstractCard
{

  // Chaturanga
  // - 3rd edition:
  //   - Meld a card with a bonus from your hand. If you do, draw two cards of value equal to that
  //     card's bonus. Otherwise, draw and foreshadow a card of value equal to the number of top
  //     cards on your board.
  // - 4th edition:
  //   - Meld a card from your hand. Draw and foreshadow a card of value equal to the melded card.
  //   - If Chaturanga was foreseen, draw and foreshadow a card of value equal to the number of
  //     colors on your board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::wasForeseen()) {
      self::drawAndForeshadow(count(self::getTopCards()));
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'    => 'hand',
      'meld_keyword'     => true,
      'with_bonus'       => self::isFirstOrThirdEdition(),
      'reveal_if_unable' => self::isFirstOrThirdEdition(),
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstOrThirdEdition()) {
      $bonus = self::getBonusIcon($card);
      self::draw($bonus);
      self::draw($bonus);
    } else {
      self::drawAndForeshadow(self::getValue($card));
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstOrThirdEdition() && self::getNumChosen() === 0) {
      self::drawAndForeshadow(count(self::getTopCards()));
    }
  }

}