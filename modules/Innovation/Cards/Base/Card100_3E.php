<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card100_3E extends AbstractCard
{
  // Self Service (3rd edition):
  //   - Execute each of the non-demand dogma effects of any other top card on your board. Do not share them.
  //   - If you have more achievements than each other player, you win.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      $numAchievements = $this->game->getPlayerNumberOfAchievements(self::getPlayerId());
      $hasMostAchievements = true;
      foreach (self::getOpponentIds() as $opponentId) {
        if ($this->game->getPlayerNumberOfAchievements($opponentId) > $numAchievements) {
          $hasMostAchievements = false;
        }
      }
      if ($hasMostAchievements) {
        if ($this->game->isTeamGame()) {
          self::notifyTeam(clienttranslate('Your team has more achievements than the other team.'));
          self::notifyOtherTeam(clienttranslate('The other team has more achievements than yours.'));
        } else {
          self::notifyPlayer(clienttranslate('${You} have more achievements than each other player.'));
          self::notifyOthers(clienttranslate('${player_name} has more achievements than each other player.'));
        }
        self::win();
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_from' => Locations::BOARD,
      // Exclude the card currently being executed (it's possible for the effects of Self Service to be executed as if it were on another card)
      'not_id'      => $this->game->getCurrentNestedCardState()['executing_as_if_on_card_id'],
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

}