<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card6 extends AbstractCard
{
  // Clothing
  // - 3rd edition:
  //   - Meld a card from your hand of different color from any card on your board.
  //   - Draw and score a [1] for each color present on your board not present on any opponent's board.
  // - 4th edition:
  //   - Meld a card from your hand of a color not on your board.
  //   - Draw and score a [1] for each color present on your board that no opponent has on their board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      $numCardsToScore = 0;
      $boards = $this->game->getBoards(self::getPlayerIds());
      foreach (Colors::ALL as $color) { // Evaluate each color
        if (!$boards[self::getPlayerId()][$color]) {
          continue;
        }
        $presentOnOpposingBoard = false;
        foreach (self::getOpponentIds() as $opponentId) {
          if ($boards[$opponentId][$color]) {
            $presentOnOpposingBoard = true;
            break;
          }
        }
        if (!$presentOnOpposingBoard) { // The opponents do not have this color => point
          $numCardsToScore++;
        }
      }

      $args = ['i18n' => ['n'], 'n' => self::renderNumber($numCardsToScore)];
      self::notifyPlayer(clienttranslate('${You} have ${n} color(s) present on your board not present on any opponent\'s board.'), $args);
      self::notifyOthers(clienttranslate('${player_name} has ${n} color(s) present on his board not present on any of his opponents\' boards.'), $args);

      for ($i = 0; $i < $numCardsToScore; $i++) {
        self::drawAndScore(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    $stacks = self::getCardsKeyedByColor(Locations::BOARD);
    $colors = [];
    foreach (Colors::ALL as $color) {
      if (!$stacks[$color]) {
        $colors[] = $color;
      }
    }
    return [
      'location_from' => Locations::HAND,
      'meld_keyword'  => true,
      'color'         => $colors,
    ];
  }

}
