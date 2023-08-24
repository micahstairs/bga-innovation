<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card22 extends Card
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
      $renderedIcon = self::renderIcon($this->game::HEALTH);
      $numToDraw = 0;
      if (self::isFirstOrThirdEdition()) {
        $iconCount = self::getStandardIconCount($this->game::HEALTH);
        self::notifyPlayer(
          clienttranslate('${You} have ${n} visible ${icon} on your board.'),
          ['You' => 'You', 'n' => $iconCount, 'icon' => $renderedIcon]
        );
        self::notifyOthers(
          clienttranslate('${player_name} has visible ${n} ${icon} on his board.'),
          ['player_name' => $this->game->getColoredPlayerName(self::getPlayerId()), 'n' => $iconCount, 'icon' => $renderedIcon]
        );
        $numToDraw = $this->game->intDivision($iconCount, 2);
      } else {
        for ($color = 0; $color < 5; $color++) {
          if ($this->game->boardPileHasRessource(self::getPlayerId(), $color, $this->game::HEALTH)) {
            $numToDraw++;
          }
        }
        self::notifyPlayer(
          clienttranslate('${You} have ${n} color(s) with one or more visible ${icon}.'),
          ['i18n' => ['n'], 'You' => 'You', 'n' => $this->game->getTranslatedNumber($numToDraw), 'icon' => $renderedIcon]
        );
        self::notifyOthers(
          clienttranslate('${player_name} has ${n} color(s) with one or more visible ${icon}.'),
          [
            'i18n'        => ['n'],
            'player_name' => $this->game->getColoredPlayerName(self::getPlayerId()),
            'n'           => $this->game->getTranslatedNumber($numToDraw),
            'icon'        => $renderedIcon,
          ]
        );
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
      'color'         => [$this->game::GREEN],
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      self::junkBaseDeck(2);
      self::junk($this->game->getIfTopCardOnBoard(self::getCardIdFromClassName()));
    }
  }

}