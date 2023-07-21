<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card509 extends Card
{

  // Cliffhanger:
  //   - Reveal a [4] in your safe. If it is: green, tuck it; purple, meld it; red, achieve it
  //     regardless of eligibility; yellow, score it; blue, draw a [5]. If you cannot, safeguard
  //     the top card of the [4] deck.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'safe',
      'location_to'   => 'revealed',
      'age'           => 4,
    ];
  }

  public function handleCardChoice(array $card)
  {
    switch ($card['color']) {
      case $this->game::BLUE:
        self::draw(5);
        self::safeguard($card);
        break;
      case $this->game::RED:
        $this->game->transferCardFromTo($card, self::getPlayerId(), 'achievements');
        break;
      case $this->game::GREEN:
        self::tuck($card);
        break;
      case $this->game::YELLOW:
        self::score($card);
        break;
      case $this->game::PURPLE:
        self::meld($card);
        break;
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      self::safeguard($this->game->getDeckTopCard(4, $this->game::BASE));
    }
  }
}