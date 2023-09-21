<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;

class Card509 extends AbstractCard
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
      case Colors::BLUE:
        self::draw(5);
        self::safeguard($card);
        break;
      case Colors::RED:
        self::achieve($card);
        break;
      case Colors::GREEN:
        self::tuck($card);
        break;
      case Colors::YELLOW:
        self::score($card);
        break;
      case Colors::PURPLE:
        self::meld($card);
        break;
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      $card = $this->game->getDeckTopCard(4, CardTypes::BASE);
      $this->game->transferCardFromTo($card, self::getPlayerId(), 'safe', ['draw_keyword' => false]);
    }
  }
}