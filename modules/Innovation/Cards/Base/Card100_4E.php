<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Locations;

class Card100_4E extends AbstractCard
{
  // Self Service (4th edition):
  //   - If you have at least twice as many achievements as each opponent, you win.
  //   - Self-execute any top card other than Self Service on your board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::hasTwiceAsManyAchievements()) {
        if ($this->game->isTeamGame()) {
          self::notifyTeam(clienttranslate('Your team has at least twice an many achievements than the other team.'));
          self::notifyOtherTeam(clienttranslate('The other team has at least twice as many achievements as yours.'));
        } else {
          self::notifyPlayer(clienttranslate('${You} have at least twice as many achievements as your opponents.'));
          self::notifyOthers(clienttranslate('${player_name} has at least twice as many achievements as his opponents.'));
        }
        self::win();
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->chooseCardFrom(Locations::BOARD)->otherThan(CardIds::SELF_SERVICE)->build();
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

  private function hasTwiceAsManyAchievements(): bool
  {
    $numAchievements = $this->game->getPlayerNumberOfAchievements(self::getPlayerId());
    foreach (self::getOpponentIds() as $opponentId) {
      if ($numAchievements < $this->game->getPlayerNumberOfAchievements($opponentId) * 2) {
        return false;
      }
    }
    return true;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::getId($card) != CardIds::SELF_SERVICE) {
        return true;
      }
    }
    return self::hasTwiceAsManyAchievements();
  }

}