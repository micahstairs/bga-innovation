<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card122 extends Card
{

  // Mask of Warka
  // - 3rd edition:
  //   - Choose a color. Each player reveals all cards of that color from their hand. If you are
  //     the only player to reveal cards, return them and claim all achievements of value matching
  //     those cards, ignoring eligibility.
  // - 4th edition:
  //   - Choose a color. Each player reveals their hand. If you are the only player to reveal
  //     cards of that color, return them and claim all achievements of value matching those
  //     cards, ignoring eligibility.

  public function initialExecution()
  {
    self::setAuxiliaryArray([]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'hand',
        'return_keyword' => true,
        'color'          => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function handleSpecialChoice(int $color)
  {
    $playerRevealed = false;
    $otherPlayerRevealed = false;
    foreach (self::getPlayerIds() as $playerId) {
      self::revealHand($playerId);
      if (self::countCardsKeyedByColor('hand')[$color] > 0) {
        if ($playerId == self::getPlayerId()) {
          $playerRevealed = true;
        } else {
          $otherPlayerRevealed = true;
        }
      }
    }

    $args = ['i18n' => ['color'], 'color' => self::renderColor($color)];
    if ($playerRevealed && !$otherPlayerRevealed) {
      self::notifyPlayer(clienttranslate('No other player revealed a ${color} card.'), $args);
      self::notifyOthers(
        clienttranslate('No player other than ${player_name} revealed a ${color} card.'),
        array_merge($args, ['player_name' => self::renderPlayerName()])
      );
      self::setAuxiliaryValue($color); // Track color to return
      self::setMaxSteps(2);
    } else if ($playerRevealed && $otherPlayerRevealed) {
      self::notifyAll(clienttranslate('More than one player revealed a ${color} card.'), $args);
    } else {
      self::notifyPlayer(
        clienttranslate('${You} did not reveal a ${color} card.'),
        array_merge($args, ['You' => 'You'])
      );
      self::notifyOthers(
        clienttranslate('${player_name} did not reveal a ${color} card.'),
        array_merge($args, ['player_name' => self::renderPlayerName()])
      );
    }
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray($card['age']);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $values = array_unique(self::getAuxiliaryArray());
      $achievementsByValue = self::getCardsKeyedByValue('achievements', 0);
      $achievementWasClaimed = false;
      foreach ($values as $value) {
        foreach ($achievementsByValue[$value] as $achievement) {
          self::achieve($achievement);
          $achievementWasClaimed = true;
        }
      }
      if (!$achievementWasClaimed) {
        self::notifyAll(clienttranslate('There are no claimable achievements matching the returned values.'));
      }
    }
  }

}