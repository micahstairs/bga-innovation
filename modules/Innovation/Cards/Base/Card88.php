<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Locations;

class Card88 extends AbstractCard
{
  // Fission:
  // - 3rd edition:
  //   - I DEMAND you draw a [10]! If it is red, remove all hands, boards, and score piles from the
  //     game! If this occurs, the dogma action is complete.
  //   - Return a top card other than Fission from any player's board. Draw a [10].
  // - 4th edition:
  //   - I DEMAND you draw a [10]! If it is red, junk each player's non-achievement cards, and the
  //     Dogma action is complete!
  //   - Return a top card other than Fission from any player board.
  //   - Draw a [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      $card = self::drawAndReveal(10);
      $this->notifications->notifyCardColor($card['color']);
      if (self::isRed($card)) {
        $this->game->setStat(true, 'fission_triggered');
        $cards = [];
        foreach (self::getPlayerIds() as $player) {
          $cards = array_merge($cards, self::getCards(Locations::HAND), $player);
          $cards = array_merge($cards, self::getCards(Locations::BOARD), $player);
          $cards = array_merge($cards, self::getCards(Locations::SCORE), $player);
          $cards = array_merge($cards, self::getCards(Locations::REVEALED), $player);
          if (self::isFourthEdition()) {
            $cards = array_merge($cards, self::getCards(Locations::DISPLAY), $player);
            $cards = array_merge($cards, self::getCards(Locations::FORECAST), $player);
            $cards = array_merge($cards, self::getCards(Locations::SAFE), $player);
          }
        }
        if (self::isFourthEdition()) {
          self::junkCards($cards);
          self::notifyAll(clienttranslate('Each player\'s non-achievement cards are junked.'));
        } else {
          self::removeCards($cards);
          self::notifyAll(clienttranslate('All hands, boards and score piles are removed from the game. Achievements are kept.'));
        }

        // Set the flags as if the launcher had completed the non-demand dogma effect
        $this->game->DbQuery(
          $this->game->format("
            UPDATE
                nested_card_execution
            SET
                current_player_id = {player_id},
                current_effect_type = 1,
                current_effect_number = 2
            WHERE
                nesting_index = {nesting_index}",
            array('player_id' => self::getLauncherId(), 'nesting_index' => $this->game->innovationGameState->get('current_nesting_index'))
          )
        );
      } else {
        self::transferToHand($card);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::draw(10);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'return_keyword' => true,
      'location_from'  => Locations::BOARD,
      'not_id'         => CardIds::FISSION,
      'owner_from'     => 'any player',
    ];
  }

  public function atEndOfEffect()
  {
    if (self::isFirstNonDemand() && self::isFirstOrThirdEdition()) {
      self::draw(10);
    }
  }

}