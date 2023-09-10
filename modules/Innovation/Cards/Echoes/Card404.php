<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card404 extends Card
{

  // Saxophone
  //   - You may splay your purple cards up.
  //   - If the [IMAGE] for Bell, Flute, Piano, and Saxophone are visible anywhere, you win. 
  //     Otherwise, draw a [7] for each [IMAGE] that is visible.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
      return;
    }

    $numCardsToDraw = 0;
    if (self::bottomLeftIconVisible(self::getCard(CardIds::BELL))) {
      $numCardsToDraw++;
    }
    if (self::bottomLeftIconVisible(self::getCard(CardIds::FLUTE))) {
      $numCardsToDraw++;
    }
    if (self::bottomCenterIconVisible(self::getCard(CardIds::PIANO))) {
      $numCardsToDraw++;
    }
    if (self::bottomCenterIconVisible(self::getCard(CardIds::SAXOPHONE))) {
      $numCardsToDraw++;
    }

    self::notifyAll(
      clienttranslate('There is ${n} ${icon} visible across all player boards.'),
      ['n' => $numCardsToDraw, 'icon' => $this->game->getMusicNoteIcon()]
    );

    if ($numCardsToDraw === 4) {
      self::win();
    } else {
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        self::draw(7);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::UP,
      'color'           => [Colors::PURPLE],
    ];
  }

  private function bottomLeftIconVisible($card)
  {
    return $this->game->getIfTopCardOnBoard($card['id']) || ($card['location'] == 'board' && $card['splay_direction'] >= Directions::RIGHT);
  }

  private function bottomCenterIconVisible($card)
  {
    return $this->game->getIfTopCardOnBoard($card['id']) || ($card['location'] == 'board' && $card['splay_direction'] >= Directions::UP);
  }

}