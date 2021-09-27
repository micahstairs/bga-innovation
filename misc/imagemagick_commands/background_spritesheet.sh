#!/bin/bash
folder_path="../card_backgrounds"

magick montage $folder_path/{blue_cropped,red_cropped,green_cropped,yellow_cropped,purple_cropped,special_achievement,action_1,action_2}.png -tile 8x1 -resize 560x408 -geometry +5+5 -background 'white' $folder_path/background_spritesheet.png
