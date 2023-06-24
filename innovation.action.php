<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Innovation implementation : © Jean Portemer <jportemer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * innovation.action.php
 *
 * Innovation main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic.
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/innovation/innovation/myAction.html", ...)
 *
 */
  
  
  class action_innovation extends APP_GameAction {

    // Constructor: please do not modify
    public function __default() {
        if(self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        } else {
            $this->view = "innovation_innovation";
            self::trace( "Complete reinitialization of board game" );
        }
    } 

    public function debug_transfer() {
        self::setAjaxMode();
        $card_id = self::getArg("card_id", AT_posint, true);
        $transfer_action = self::getArg("transfer_action", AT_alphanum, true);
        $this->game->debug_transfer($card_id, $transfer_action);
        self::ajaxResponse();
    }

    public function debug_transfer_all() {
        self::setAjaxMode();
        $location_from = self::getArg("location_from", AT_alphanum, true);
        $location_to = self::getArg("location_to", AT_alphanum, true);
        $this->game->debug_transfer_all($location_from, $location_to);
        self::ajaxResponse();
    }

    public function debug_splay() {
        self::setAjaxMode();
        $color = self::getArg("color", AT_posint, true);
        $direction = self::getArg("direction", AT_posint, true);
        $this->game->debug_splay($color, $direction);
        self::ajaxResponse();
    }
      
    public function initialMeld() {
        self::setAjaxMode();
        
        // Retrieve arguments
        
        $card_id = self::getArg("card_id", AT_posint, true);
        // Call initialMeld from game logic
        $this->game->initialMeld($card_id);
        
        self::ajaxResponse();
    }

    public function updateInitialMeld() {
        self::setAjaxMode();
        
        // Retrieve arguments
        
        $card_id = self::getArg("card_id", AT_posint, true);
        // Call updateInitialMeld from game logic
        $this->game->updateInitialMeld($card_id);
        
        self::ajaxResponse();
    }

    public function passSeizeRelic() {
        self::setAjaxMode();
        
        // Call passSeizeRelic from game logic
        $this->game->passSeizeRelic();
        
        self::ajaxResponse();
    }

    public function seizeRelicToHand() {
        self::setAjaxMode();
        
        // Call seizeRelicToHand from game logic
        $this->game->seizeRelicToHand();
        
        self::ajaxResponse();
    }

    public function seizeRelicToAchievements() {
        self::setAjaxMode();
        
        // Call seizeRelicToAchievements from game logic
        $this->game->seizeRelicToAchievements();
        
        self::ajaxResponse();
    }

    public function dogmaArtifactOnDisplay() {
        self::setAjaxMode();
        
        // Call dogmaArtifactOnDisplay from game logic
        $this->game->dogmaArtifactOnDisplay();
        
        self::ajaxResponse();
    }

    public function returnArtifactOnDisplay() {
        self::setAjaxMode();
        
        // Call returnArtifactOnDisplay from game logic
        $this->game->returnArtifactOnDisplay();
        
        self::ajaxResponse();
    }

    public function passArtifactOnDisplay() {
        self::setAjaxMode();
        
        // Call passArtifactOnDisplay from game logic
        $this->game->passArtifactOnDisplay();
        
        self::ajaxResponse();
    }

    public function passPromoteCard() {
        self::setAjaxMode();
        
        // Call passPromoteCard from game logic
        $this->game->passPromoteCard();
        
        self::ajaxResponse();
    }

    public function promoteCard() {
        self::setAjaxMode();

        // Retrieve arguments
        $card_id = self::getArg("card_id", AT_posint, true);
        
        // Call promoteCard from game logic
        $this->game->promoteCard($card_id);
        
        self::ajaxResponse();
    }

    public function promoteCardBack() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $owner = self::getArg("owner", AT_posint, true);
        $location = self::getArg("location", AT_alphanum, true);
        $age = self::getArg("age", AT_posint, true);
        $type = self::getArg("type", AT_posint, true);
        $is_relic = self::getArg("is_relic", AT_posint, true);
        $position = self::getArg("position", AT_posint, true);
        
        // Call promoteCardBack from game logic
        $this->game->promoteCardBack($owner, $location, $age, $type, $is_relic, $position);
        
        self::ajaxResponse();
    }

    public function passDogmaPromotedCard() {
        self::setAjaxMode();
        
        // Call passDogmaPromotedCard from game logic
        $this->game->passDogmaPromotedCard();
        
        self::ajaxResponse();
    }

    public function dogmaPromotedCard() {
        self::setAjaxMode();
        
        // Call dogmaPromotedCard from game logic
        $this->game->dogmaPromotedCard();
        
        self::ajaxResponse();
    }
    
    public function achieve() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $age = self::getArg("age", AT_posint, true);
        
        // Call achieve from game logic
        $this->game->achieve($age);
        
        self::ajaxResponse();
    }

    public function achieveCardBack() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $owner = self::getArg("owner", AT_posint, true);
        $location = self::getArg("location", AT_alphanum, true);
        $age = self::getArg("age", AT_posint, true);
        $type = self::getArg("type", AT_posint, true);
        $is_relic = self::getArg("is_relic", AT_posint, true);
        $position = self::getArg("position", AT_posint, true);
        
        // Call achieveCardBack from game logic
        $this->game->achieveCardBack($owner, $location, $age, $type, $is_relic, $position);
        
        self::ajaxResponse();
    }
    
    public function draw() {
        self::setAjaxMode();
        
        // No argument
        
        // Call draw from game logic
        $this->game->draw();
        
        self::ajaxResponse();
    }
    
    public function meld() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $card_id = self::getArg("card_id", AT_posint, true);
        
        // Call meld from game logic
        $this->game->meld($card_id);
        
        self::ajaxResponse();
    }
    
    public function dogma() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $card_id = self::getArg("card_id", AT_posint, true);
        $card_id_to_return = self::getArg("card_id_to_return", AT_posint, false, null);
        
        // Call dogma from game logic
        $this->game->dogma($card_id, $card_id_to_return);
        
        self::ajaxResponse();
    }

    public function endorse() {
        self::setAjaxMode();

        // Retrieve arguments
        $card_to_endorse_id = self::getArg("card_to_endorse_id", AT_posint, true);
        $payment_card_id = self::getArg("payment_card_id", AT_posint, true);

        // Call endorse from game logic
        $this->game->endorse($card_to_endorse_id, $payment_card_id);

        self::ajaxResponse();
    }

    public function choose() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $card_id = self::getArg("card_id", AT_int, true); // -1 if no choice
        
        // Call choose from game logic
        $this->game->choose($card_id);
        
        self::ajaxResponse();
    }
    
    public function chooseRecto() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $owner = self::getArg("owner", AT_posint, true);
        $location = self::getArg("location", AT_alphanum, true);
        $age = self::getArg("age", AT_posint, true);
        $type = self::getArg("type", AT_posint, true);
        $is_relic = self::getArg("is_relic", AT_posint, true);
        $position = self::getArg("position", AT_posint, true);
        
        // Call choose from game logic
        $this->game->chooseRecto($owner, $location, $age, $type, $is_relic, $position);
        
        self::ajaxResponse();
    }
    
    public function chooseSpecialOption() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $choice = self::getArg("choice", AT_posint, true);
        
        // Call chooseSpecialOption from game logic
        $this->game->chooseSpecialOption($choice);
        
        self::ajaxResponse();
    }
    
    public function publicationRearrange() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $color = self::getArg("color", AT_posint, true);
        $permutations_done_raw = self::getArg("permutations_done", AT_numberlist, true);
        
        // Reshape them a bit
        $permutations_done = explode(';', $permutations_done_raw);
        foreach($permutations_done as &$group) {
            $group = explode(',', $group);
            $group = array('position' => $group[0], 'delta' => $group[1]);
        }
        
        $choice = array('color' => $color, 'permutations_done' => $permutations_done);
        
        // Call chooseSpecialOption from game logic
        $this->game->chooseSpecialOption($choice);
        
        self::ajaxResponse();
    }
    
    public function updateDisplayMode() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $display_mode = self::getArg("display_mode", AT_bool, true);
        
        // Call updateDisplayMode from game logic
        $this->game->updateDisplayMode($display_mode);
        
        self::ajaxResponse();
    }
    
    public function updateViewFull() {
        self::setAjaxMode();
        
        // Retrieve arguments
        $view_full = self::getArg("view_full", AT_bool, true);
        
        // Call updateDisplayMode from game logic
        $this->game->updateViewFull($view_full);
        
        self::ajaxResponse();
    }
    
    /*
    
    Example:
      
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg("myArgument1", AT_posint, true);
        $arg2 = self::getArg("myArgument2", AT_posint, true);

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse();
    }
    
    */

  }
  

