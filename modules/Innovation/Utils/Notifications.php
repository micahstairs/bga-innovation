<?php

namespace Innovation\Utils;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

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
    self::notifyGeneralInfo(clienttranslate('It has a ${icon}.'), ['icon' => Icons::render($icon)]);
  }

  public function notifyAbsenceOfIcon(int $icon)
  {
    self::notifyGeneralInfo(clienttranslate('It does not have a ${icon}.'), ['icon' => Icons::render($icon)]);
  }

  public function notifyCardColor(int $color)
  {
    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($color)));
  }

  // MISCELLANEOUS NOTIFICATIONS

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

  public function renderPlayerName($playerId): string
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
      ['i18n' => ['location'], 'player_name' => self::renderPlayerName($playerId), 'location' => $location],
    );
  }

  public function notifyPlayerLoses(int $playerId): void
  {
    self::notifyPlayer($playerId, 'log', clienttranslate('${You} lose.'),  ['You' => 'You']);
    self::notifyAllPlayersBut($playerId, 'log', clienttranslate('${player_name} loses.'), array(
      'player_name' => self::renderPlayerName($playerId)
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