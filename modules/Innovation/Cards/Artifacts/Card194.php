<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card194 extends AbstractCard
{
  // '30 World Cup Final Ball
  // - 3rd edition:
  //   - I COMPEL you to return one of your achievements!
  //   - Draw and reveal an [8]. The single player with the highest top card of the drawn card's
  //     color achieves it, ignoring eligibility. If that happens, repeat this effect.
  // - 4th edition:
  //   - I COMPEL you to return one of your claimed standard achievements!
  //   - Draw and reveal an [8]. The single player with the highest top card of the drawn card's
  //     color achieves the drawn card, ignoring eligibility. If that happens, repeat this effect.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      do {
        $card = self::drawAndReveal(8);
        $color = $card['color'];
        $playerIds = $this->game->getOwnersOfTopCardWithColorAndAge($color, $this->game->getMaxAgeOfTopCardOfColor($color));
        if (count($playerIds) === 1) {
          $playerId = $playerIds[0];
          $args = ['i18n' => ['color'], 'color' => Colors::render($color)];
          self::notifyPlayer(clienttranslate('${You} have the highest top ${color} card.'), $args, $playerId);
          self::notifyOthers(clienttranslate('${player_name} has the highest top ${color} card.'), $args, $playerId);
          self::achieve($card, $playerId);
        } else {
          self::transferToHand($card);
          return;
        }
      } while (true);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'achievements',
        'return_keyword' => true,
        'include_relics' => false,
    ];
  }

}