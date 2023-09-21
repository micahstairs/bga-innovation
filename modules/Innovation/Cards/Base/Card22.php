<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card22 extends AbstractCard
{

  // Fermenting:
  // - 3rd edition:
  //   - Draw a [2] for every two [HEALTH] on your board.
  // - 4th edition:
  //   - Draw a [2] for every color on your board with one or more [HEALTH].
  //   - You may tuck a green card from your hand. If you don't, junk all cards in the [2] deck,
  //     and junk Fermenting if it is a top card on any board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $renderedIcon = Icons::render(Icons::HEALTH);
      $numToDraw = 0;
      if (self::isFirstOrThirdEdition()) {
        $iconCount = self::getStandardIconCount(Icons::HEALTH);
        $args = ['n' => $iconCount, 'icon' => $renderedIcon];
        self::notifyPlayer(clienttranslate('${You} have ${n} visible ${icon} on your board.'), $args);
        self::notifyOthers(clienttranslate('${player_name} has visible ${n} ${icon} on his board.'), $args);
        $numToDraw = $this->game->intDivision($iconCount, 2);
      } else {
        foreach (Colors::ALL as $color) {
          if ($this->game->boardPileHasRessource(self::getPlayerId(), $color, Icons::HEALTH)) {
            $numToDraw++;
          }
        }
        $args = ['i18n' => ['n'], 'n' => self::renderNumber($numToDraw), 'icon' => $renderedIcon];
        self::notifyPlayer(clienttranslate('${You} have ${n} color(s) with one or more visible ${icon}.'), $args);
        self::notifyOthers(clienttranslate('${player_name} has ${n} color(s) with one or more visible ${icon}.'), $args);
      }
      for ($i = 0; $i < $numToDraw; $i++) {
        self::draw(2);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'      => true,
      'location_from' => 'hand',
      'tuck_keyword'  => true,
      'color'         => [Colors::GREEN],
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      self::junkBaseDeck(2);
      self::junk($this->game->getIfTopCardOnBoard(CardIds::FERMENTING));
    }
  }

}