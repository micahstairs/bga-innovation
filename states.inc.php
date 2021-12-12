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
 * states.inc.php
 *
 * Innovation game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

// Game State Diagram: https://docs.google.com/drawings/d/1oKv79jHr1t2C8VjWJCkfSAN8FoGzT8rkM9hVooFV4S0
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => clienttranslate('Game setup'),
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),
    
    // Note: ID=2 => your first state

    2 => array(
        "name" => "turn0",
        "description" => clienttranslate('Some players still have to choose a card to meld'),
        "descriptionmyturn" => clienttranslate('${You} must choose a card to meld'),
        "type" => "multipleactiveplayer",
        "action" => "stTurn0",
        "args" => "argTurn0",
        "possibleactions" => array("initialMeld", "updateInitialMeld"),
        "transitions" => array("" => 3)
    ),
    
    3 => array(
        "name" => "whoBegins",
        "description" => clienttranslate('Each player meld his card...'),
        "type" => "game",
        "action" => "stWhoBegins",
        "updateGameProgression" => true,
        "transitions" => array("" => 4)
    ),

    4 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must take ${qualified_action}'),
        "descriptionmyturn" => clienttranslate('${You} must take ${qualified_action}:'),
        "type" => "activeplayer",
        "possibleactions" => array("draw", "meld", "dogma", "achieve"),
        "args" => "argPlayerTurn",
        "transitions" => array("interPlayerTurn" => 5, "dogmaEffect" => 6, "justBeforeGameEnd" => 98)
    ),
    
    5 => array(
        "name" => "interPlayerTurn",
        "description" => clienttranslate('Finalising the player action...'),
        "type" => "game",
        "action" => "stInterPlayerTurn",
        "updateGameProgression" => true,
        "transitions" => array("playerTurn" => 4, "artifactPlayerTurn" => 15)
    ),

    6 => array(
        "name" => "dogmaEffect",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect}...'),
        "type" => "game",
        "action" => "stDogmaEffect",
        "args" => "argDogmaEffect",
        "transitions" => array("playerInvolvedTurn" => 8, "interDogmaEffect" => 7)
    ),
    
    7 => array(
        "name" => "interDogmaEffect",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect}...'),
        "type" => "game",
        "action" => "stInterDogmaEffect",
        "args" => "argInterDogmaEffect",
        "updateGameProgression" => true,
        "transitions" => array("dogmaEffect" => 6, "interPlayerTurn" => 5, "justBeforeGameEnd" => 98)
    ),
    
    8 => array(
        "name" => "playerInvolvedTurn",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stPlayerInvolvedTurn",
        "args" => "argPlayerInvolvedTurn",
        "transitions" => array("interPlayerInvolvedTurn" => 9, "interactionStep" => 10, "justBeforeGameEnd" => 98)
    ),
    
    9 => array(
        "name" => "interPlayerInvolvedTurn",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stInterPlayerInvolvedTurn",
        "args" => "argInterPlayerInvolvedTurn",
        "updateGameProgression" => true,
        "transitions" => array("playerInvolvedTurn" => 8, "interDogmaEffect" => 7)
    ),
    
    10 => array(
        "name" => "interactionStep",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stInteractionStep",
        "args" => "argInteractionStep",
        "transitions" => array("preSelectionMove" => 12, "interPlayerInvolvedTurn" => 9, "interactionStep" => 10, "justBeforeGameEnd" => 98)
    ),
    
    11 => array(
        "name" => "interInteractionStep",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stInterInteractionStep",
        "args" => "argInterInteractionStep",
        "updateGameProgression" => true,
        "transitions" => array("interactionStep" => 10, "interPlayerInvolvedTurn" => 9, "justBeforeGameEnd" => 98)
    ),
    
    12 => array(
        "name" => "preSelectionMove",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stPreSelectionMove",
        "args" => "argPreSelectionMove",
        "transitions" => array("selectionMove" => 13, "interInteractionStep" => 11)
    ),
    
    13 => array(
        "name" => "selectionMove",
        "description" => clienttranslate('${card_name}: ${message_for_others}'), // The content is generated in argSelectionMove
        "descriptionmyturn" => clienttranslate('${card_name}: ${message_for_player}') . ' ', // The content is generated in argSelectionMove
        "type" => "activeplayer",
        "possibleactions" => array("choose"),
        "args" => "argSelectionMove",
        "transitions" => array("interSelectionMove" => 14, "interInteractionStep" => 11 /* for zombie only*/, "justBeforeGameEnd" => 98)
    ),
    
    14 => array(
        "name" => "interSelectionMove",
        "description" => clienttranslate('Resolving the ${card_name} ${qualified_effect} on ${player}...'),
        "type" => "game",
        "action" => "stInterSelectionMove",
        "args" => "argInterSelectionMove",
        "updateGameProgression" => true,
        "transitions" => array("preSelectionMove" => 12, "interInteractionStep" => 11, "justBeforeGameEnd" => 98)
    ),
    
    98 => array(
        "name" => "justBeforeGameEnd",
        "description" => clienttranslate('Finishing the game...'),
        "type" => "game",
        "action" => "stJustBeforeGameEnd",
        "updateGameProgression" => true,
        "transitions" => array("" => 99)
    ),

    15 => array(
        "name" => "artifactPlayerTurn",
        "description" => clienttranslate('${actplayer} must choose what to do with his Artifact on display'),
        "descriptionmyturn" => clienttranslate('${You} must choose what to do with your Artifact on display:'),
        "type" => "activeplayer",
        "possibleactions" => array("dogmaArtifactOnDisplay", "returnArtifactOnDisplay", "passArtifactOnDisplay"),
        "transitions" => array("playerTurn" => 4, "dogmaEffect" => 6, "justBeforeGameEnd" => 98)
    ),
   
    // Final state.
    // Please do not modify.
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate('End of game'),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);


