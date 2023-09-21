<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Directions;

class Card136 extends AbstractCard
{

  // Charter of Liberties (3rd edition)
  //   - Tuck a card from your hand. If you do, splay left its color, then choose a splayed color
  //     on any player's board. Execute all of that color's top card's non-demand effects, without
  //     sharing.
  // Yata No Kagami (4th edition)
  //   - Reveal a card from your hand. If you do, splay left its color on your board, then choose a
  //     top card other than Yata No Kagami of that color on any board and self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $keyword = self::isFirstOrThirdEdition() ? 'tuck_keyword' : 'reveal_keyword';
      return [
        'location_from' => 'hand',
        $keyword        => true,
      ];
    } else if (self::isFirstOrThirdEdition()) {
      return [
        'owner_from'          => 'any player',
        'choose_from'         => 'board',
        'has_splay_direction' => [Directions::LEFT, Directions::RIGHT, Directions::UP, Directions::ASLANT],
      ];
    } else {
      return [
        'owner_from'  => 'any player',
        'choose_from' => 'board',
        'color'       => self::getAuxiliaryValue(),
        'not_id'      => CardIds::YATA_NO_KAGAMI,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction() && self::getNumChosen() > 0) {
      if (self::isFourthEdition()) {
        self::transferToHand($card);
        self::setAuxiliaryValue($card['color']); // Track color to self-execute
      }
      self::splayLeft($card['color']);
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction()) {
      self::selfExecute($card);
    }
  }

}