#!/bin/bash

folder_path="../card_backgrounds"
magick montage $folder_path/special_achievement_{1..5}.png  $folder_path/action_{1,2}.png -tile 7x1 -geometry +5+5 -background 'white' $folder_path/special_achievement_card_spritesheet.png

