<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card521 extends Card
{

  // April Fool's Day:
  //   - Transfer the highest cards from your hand and score pile together to the 
  //     board of the player on your right. If you don't, claim the Folklore achievement.
  //   - Splay your yellow cards right, and unsplay your purple cards, or vice versa.
  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      $max_age_score = $this->game->getMaxAgeInScore(self::getPlayerId());
      $max_age_hand = $this->game->getMaxAgeInHand(self::getPlayerId());
      if ($max_age_score == 0 && $max_age_hand == 0) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), 598);
      } else {
        if ($max_age_score > $max_age_hand) {
          self::setAuxiliaryValue($max_age_score);
        } else {
          self::setAuxiliaryValue($max_age_hand);
        }
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'n'             => 'all',
        'location_from' => 'hand,score',
        'owner_to'      => $this->game->getActivePlayerIdOnRightOfActingPlayer(),
        'location_to'   => 'board',
        'age'           => self::getAuxiliaryValue(),
      ];
    } else {
      return ['choices' => [0, 1]];
    }
  }

  public function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Splay purple right and unsplay yellow'),
      1 => clienttranslate('Splay yellow right and unsplay purple'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      self::splayRight($this->game::YELLOW);
      self::unsplay($this->game::PURPLE);
    } else {
      self::splayRight($this->game::PURPLE);
      self::unsplay($this->game::YELLOW);
    }
  }

}