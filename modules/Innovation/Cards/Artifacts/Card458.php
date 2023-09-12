<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card458 extends Card
{

  // Jumbo Kingdom
  //   - Choose a color on your board. Junk all cards of that color from all boards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_color' => true,
      'color'        => self::getUniqueColors(Locations::BOARD),
    ];
  }

  public function handleSpecialChoice(int $color)
  {
    foreach (self::getPlayerIds() as $playerId) {
      foreach (self::getCardsKeyedByColor(Locations::BOARD, $playerId)[$color] as $card) {
        self::junkAsPartOfBulkTransfer($card);
      }
    }
    $args = ['i18n' => ['color'], 'color' => Colors::render($color)];
    self::notifyPlayer(clienttranslate('${You} junked all ${color} cards from all boards.'), array_merge($args, ['You' => 'You']));
    self::notifyOthers(clienttranslate('${player_name} junked all ${color} cards from all boards.'), array_merge($args, ['player_name' => self::renderPlayerName()]));
  }

}