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
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/innovation/innovation/myAction.html", ...)
 *
 */
  
  
  class action_innovation extends APP_GameAction
  { 
    // Constructor: please do not modify
       public function __default()
      {
          if( self::isArg( 'notifwindow') )
          {
            $this->view = "common_notifwindow";
              $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
          }
          else
          {
            $this->view = "innovation_innovation";
            self::trace( "Complete reinitialization of board game" );
      }
      } 
      
      // TODO: defines your action entry points there

    //****** DEBUG MODE: PLEASE REMOVE THIS BEFORE RELEASE
    public function debug_draw() {            
        self::setAjaxMode();
        
        // Retrieve arguments
        $card_id = self::getArg("card_id", AT_posint, true);
        // Call debug_draw from game logic
        $this->game->debug_draw($card_id);
        
        self::ajaxResponse();
    }
    //******
      
    public function initialMeld() {
        self::setAjaxMode();
        
        // Retrieve arguments
        
        $card_id = self::getArg("card_id", AT_posint, true);
        // Call initialMeld from game logic
        $this->game->initialMeld($card_id);
        
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
        
        // Call dogma from game logic
        $this->game->dogma($card_id);
        
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
        $position = self::getArg("position", AT_posint, true);
        
        // Call choose from game logic
        $this->game->chooseRecto($owner, $location, $age, $position);
        
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
  

