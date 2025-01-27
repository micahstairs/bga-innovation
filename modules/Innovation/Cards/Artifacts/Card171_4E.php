<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card171_4E extends AbstractCard
{
  // Qianlong's Dragon Robe (4th edition):
  //   - I COMPEL you to transfer your top red card to my score pile! Transfer your top green card
  //     to my board! Transfer a yellow card from your score pile to mine! Transfer a purple card
  //     from your score pile to my hand!

  public function initialExecution()
  {
    self::transferToScorePile(self::getTopCardOfColor(Colors::RED), self::getLauncherId());
    self::transferToBoard(self::getTopCardOfColor(Colors::GREEN), self::getLauncherId());
    foreach (self::getCards(Locations::SCORE) as $card) {
      self::reveal($card);
    }
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->withColor(Colors::YELLOW)->fromYourRevealed()->toMyScore()->build();
    } else {
      return self::youMust()->withColor(Colors::PURPLE)->fromYourRevealed()->toMyHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      foreach (self::getCards(Locations::REVEALED) as $card) {
        self::transferToScorePile($card);
      }
    }
  }

  public function compelMightBeEffective(): bool
  {
    // NOTE: The launcher cannot know what colors are in the player's score pile.
    return self::getTopCardOfColor(Colors::RED) || self::getTopCardOfColor(Colors::GREEN) || self::hasCards(Locations::SCORE);
  }

}