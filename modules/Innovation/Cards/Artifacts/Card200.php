<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card200 extends AbstractCard
{
  // Syncom 3
  // - 3rd edition:
  //   - Return all cards from your hand. Draw and reveal five [9]. If you revealed five colors, you win.
  // - 4th edition:
  //   - Return all cards from your hand. Draw and reveal five [9]. If you reveal five colors, you win.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => Locations::HAND,
      'return_keyword' => true,
    ];
  }

  public function afterInteraction()
  {
    for ($i = 0; $i < 5; $i++) {
      self::drawAndReveal(9);
    }

    $numColors = count(self::getUniqueColors(Locations::REVEALED));
    $args = ['i18n' => ['n'], 'n' => self::renderNumber($numColors)];
    self::notifyPlayer(clienttranslate('${You} revealed ${n} colors.'), $args);
    self::notifyOthers(clienttranslate('${player_name} revealed ${n} colors.'), $args);

    if ($numColors === 5) {
      self::win();
    } else {
      foreach (self::getCards(Locations::REVEALED) as $card) {
        self::transferToHand($card);
      }
    }
  }

}