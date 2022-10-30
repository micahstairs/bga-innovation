<?php

/**
 * For now, we can put all our includes here. Eventually, should investigate using Composer with bga-workbench
 * to allow dynamically loading all these.
 *
 * Until then, every time you create a new file in `modules/`, make sure to add the require here.
 */

require_once('modules/Innovation/Errors/CardNotFoundException.php');
require_once('modules/Innovation/Utils/Arrays.php');
require_once('modules/Innovation/Utils/Strings.php');
require_once('modules/Innovation/GameState.php');
require_once('modules/Innovation/Models/Card.php');
require_once('modules/Innovation/Cards.php');
