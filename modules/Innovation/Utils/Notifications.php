<?php

namespace Innovation\Utils;

/* Class used to handle notifications */
class Notifications
{

  protected \Innovation $game;

  function __construct(\Innovation $game)
  {
    $this->game = $game;
  }

  // CARD PROPERTY NOTIFICATIONS

  public function notifyPresenceOfIcon(int $icon)
  {
    self::notifyGeneralInfo(clienttranslate('It has a ${icon}.'), ['icon' => self::getIconSquare($icon)]);
  }

  public function notifyAbsenceOfIcon(int $icon)
  {
    self::notifyGeneralInfo(clienttranslate('It does not have a ${icon}.'), ['icon' => self::getIconSquare($icon)]);
  }

  public function notifyCardColor(int $color)
  {
    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => $this->game->getColorInClear($color)));
  }

  // CHOICE NOTIFICATIONS

  public function notifyIconChoice(int $icon, int $playerId)
  {
    $iconSquare = $this->getIconSquare($icon);
    self::notifyPlayer($playerId, 'log', clienttranslate('${You} choose ${icon}.'), ['You' => 'You', 'icon' => $iconSquare]);
    self::notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} chooses ${icon}.'), ['player_name' => self::getColoredPlayerName($playerId), 'icon' => $iconSquare]);
  }

  public function notifyValueChoice(int $value, int $playerId)
  {
    self::notifyPlayer($playerId, 'log', clienttranslate('${You} choose the value ${age}.'), ['You' => 'You', 'age' => self::renderValue($value)]);
    self::notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} chooses the value ${age}.'), ['player_name' => self::getColoredPlayerName($playerId), 'age' => self::renderValue($value)]);
  }

  public function notifyColorChoice(int $color, int $playerId)
  {
    $colorText = $this->game->getColorInClear($color);
    self::notifyPlayer($playerId, 'log', clienttranslate('${You} choose ${color}.'), ['i18n' => ['color'], 'You' => 'You', 'color' => $colorText]);
    self::notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} chooses ${color}.'), ['i18n' => ['color'], 'player_name' => self::getColoredPlayerName($playerId), 'color' => $colorText]);
  }

  public function notifyPlayerChoice(int $chosenPlayerId, int $playerId) {
    self::notifyPlayer(
      $playerId,
      'log',
      clienttranslate('${You} choose the player ${player_choice}.'),
      array(
        'You'           => 'You',
        'player_choice' => self::getColoredPlayerName($chosenPlayerId)
      )
    );
    self::notifyAllPlayersBut(
      $playerId,
      'log',
      clienttranslate('${player_name} chooses the player ${player_choice}.'),
      array(
        'player_name'   => self::getColoredPlayerName($playerId),
        'player_choice' => self::getColoredPlayerName($chosenPlayerId)
      )
    );
  }

  // MISCELLANEOUS NOTIFICATIONS

  public function getIconSquare(int $icon): string
  {
    return $this->game->getIconSquare($icon);
  }

  public function renderValue(int $value): string
  {
    return $this->game->getAgeSquare($value);
  }

  public function renderValueWithType(int $value, int $type): string
  {
    return $this->game->getAgeSquareWithType($value, $type);
  }

  public function getColoredText($text, $playerId): string
  {
    $color = $this->game->getPlayerColorFromId($playerId);
    return "<span style='font-weight: bold; color:#" . $color . ";'>" . $text . "</span>";
  }

  public function getColoredPlayerName($playerId): string
  {
    return self::getColoredText($this->game->getPlayerNameFromId($playerId), $playerId);
  }

  public function notifyLocationFull(string $location, int $playerId): void
  {
    self::notifyPlayer($playerId, 'log',
      clienttranslate('${Your} ${location} was already full so the card was not transferred to your ${location}.'),
      ['i18n' => ['location'], 'Your' => 'Your', 'location' => $location],
    );
    self::notifyAllPlayersBut($playerId, 'log',
      clienttranslate('${player_name}\'s ${location} was already full so the card was not transferred to his ${location}.'),
      ['i18n' => ['location'], 'player_name' => self::getColoredPlayerName($playerId), 'location' => $location],
    );
  }

  public function notifyPlayerLoses(int $playerId): void
  {
    self::notifyPlayer($playerId, 'log', clienttranslate('${You} lose.'),  ['You' => 'You']);
    self::notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} loses.'), array(
      'player_name' => self::getColoredPlayerName($playerId)
    ));
  }

  public function notifyTeamLoses(int $playerId1, int $playerId2): void
  {
    $this->game->notifyPlayer($playerId1, 'log', clienttranslate('${Your} team loses.'), ['Your' => 'Your']);
    $this->game->notifyPlayer($playerId2, 'log', clienttranslate('${Your} team loses.'), ['Your' => 'Your']);
    $this->game->notifyAllPlayersBut([$playerId1, $playerId2], 'log', clienttranslate('The other team loses.'));
  }

  public function notifyPlayer($playerId, $type, $message, $args)
  {
    $this->game->notifyPlayer($playerId, $type, $message, $args);
  }

  public function notifyAllPlayersBut($playerId, $type, $message, $args)
  {
    $this->game->notifyAllPlayersBut($playerId, $type, $message, $args);
  }

  public function notifyGeneralInfo($message, $args)
  {
    $this->game->notifyGeneralInfo($message, $args);
  }
}