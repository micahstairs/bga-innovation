<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card137 extends AbstractCard
{

  // Excalibur
  //   - I COMPEL you to transfer a top card of higher value than my top card of the same color
  //     from your board to my board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $colors = [];
    foreach (self::getTopCards() as $playerCard) {
      $launcherCard = self::getTopCardOfColor($playerCard['color'], self::getLauncherId());
      if ($launcherCard === null || $playerCard['faceup_age'] > $launcherCard['faceup_age']) {
        $colors[] = $playerCard['color'];
      }
    }
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => 'board',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'board',
      'color'         => $colors,
    ];
  }

}