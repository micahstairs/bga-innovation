<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card416_4E extends AbstractCard
{

  // Laser (4th edition)
  //   - Return four cards from your score pile. Return all available standard achievements.
  //   - Draw and foreshadow an [11]. Draw and meld a [10]. If Laser was foreseen, draw and meld an [11].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else {
      self::drawAndForeshadow(11);
      self::drawAndMeld(10);
      if (self::wasForeseen()) {
        self::drawAndMeld(11);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->exactly(4)->fromYourScore();
    } else {
      return self::youMust()->return()->all()->fromAvailableAchievements();
    }
  }

}