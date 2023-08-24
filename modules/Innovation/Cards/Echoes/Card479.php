<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card479 extends Card
{

  // Meritocracy
  //   - I DEMAND you transfer a top card of each color from your board to mine, each card with any
  //     icon of which there are more of on my board than yours! If you transfer a card, and
  //     Meritocracy was foreseen, repeat this effect!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $icons = self::getIconsWithFewerThanLauncher();
    $cardIds = [];
    foreach (self::getTopCards() as $card) {
      foreach (self::getIcons($card) as $icon) {
        if (in_array($icon, $icons)) {
          $cardIds[] = $card['id'];
        }
      }
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'n'                               => 'all',
      'owner_from'                      => self::getPlayerId(),
      'location_from'                   => 'board',
      'owner_to'                        => self::getLauncherId(),
      'location_to'                     => 'board',
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() >= 1 && self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

  private function getIconsWithFewerThanLauncher(): array
  {
    $icons = [];
    $counts = self::getAllIconCounts();
    foreach (self::getAllIconCounts(self::getLauncherId()) as $icon => $launcherCount) {
      if (!array_key_exists($icon, $counts) || $counts[$icon] < $launcherCount) {
        $icons[] = $icon;
      }
    }
    return $icons;
  }

}