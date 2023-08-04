<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

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
    if (self::bottomLeftIconVisible(self::getCard(342))) { // Bell
      $numCardsToDraw++;
    }
    if (self::bottomLeftIconVisible(self::getCard(343))) { // Flute
      $numCardsToDraw++;
    }
    if (self::bottomCenterIconVisible(self::getCard(383))) { // Piano
      $numCardsToDraw++;
    }
    if (self::bottomCenterIconVisible(self::getCard(404))) { // Saxophone
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
      'can_pass' => true,
        'splay_direction' => $this->game::UP,
        'color' => [$this->game::PURPLE],
    ];
  }

  private function bottomLeftIconVisible($card) {
    return $this->game->getIfTopCardOnBoard($card['id']) || ($card['location'] == 'board' && $card['splay_direction'] >= $this->game::RIGHT);
  }

  private function bottomCenterIconVisible($card) {
    return $this->game->getIfTopCardOnBoard(383) || ($card['location'] == 'board' && $card['splay_direction'] >= $this->game::UP);
  }

}