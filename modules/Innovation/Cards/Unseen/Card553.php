<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card553 extends Card
{

  // Fortune Cookie
  //   - If you have exactly seven of any icon visible on your board, 
  //     draw and score a [7]; exactly eight, splay your green or 
  //     purple cards right and draw an [8]; exactly nine, draw a [9].'),

  public function initialExecution()
  {
      $icon_ctr = array(0,0,0,0,0,0,0,0,0);
      
      for ($color = 0; $color < 5; $color++) {
        foreach ($this->game->getPlayerResourceCounts(self::getPlayerId()) as $icon => $count) {
            $icon_ctr[$icon] += $this->game->countVisibleIconsInPile(self::getPlayerId(), $icon, $color);
        }
      }
      
      $seven_flag = false;
      $eight_flag = false;
      $nine_flag = false;
      foreach($icon_ctr as $ctr) {
          if ($ctr == 7) {
              $seven_flag = true;
          } else if ($ctr == 8) {
              $eight_flag = true;
          } else if ($ctr == 9) {
              $nine_flag = true;
          }
      }
      
      if ($seven_flag) {
          self::drawAndScore(7);
      }
      if ($eight_flag) {
          self::setMaxSteps(1);
          self::setAuxiliaryValue($nine_flag);
      } else if ($nine_flag) {
          self::draw(9); // draw 9 later if 8 is
      }
  }

  public function getInteractionOptions(): array
  {
      // "splay your green or purple cards right"
      return [
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::GREEN, $this->game::PURPLE],
      ];
  }

  public function afterInteraction()
  {
    self::draw(8);
    if (self::getAuxiliaryValue() == 1) {
      self::draw(9);
    }    
  }
  
}