<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card564 extends AbstractCard
{

  // Opus Dei:
  //   - Reveal the highest card in your score pile. If you do, splay your cards of the revealed
  //     card's color up, and safeguard the revealed card.
  //   - Draw an [8] for every color on your board splayed up.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      $cardIds = $this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), Locations::SCORE);
      if (count($cardIds) >= 1) {
        self::setAuxiliaryValue(self::getValue(self::getCard($cardIds[0])));
        self::setMaxSteps(1);
      }
    } else {
      foreach (self::getTopCards() as $card) {
        if ($card['splay_direction'] == Directions::UP) {
          self::draw(8);
        }
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->reveal()->value(self::getAuxiliaryValue())->fromYourScore();
  }

  public function handleCardChoice(array $card)
  {
    self::splayUp(self::getColor($card));
    if (!self::safeguard($card)) {
      self::transferToScorePile($card);
    }
  }

}