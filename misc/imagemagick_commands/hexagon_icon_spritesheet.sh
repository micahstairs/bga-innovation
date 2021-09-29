#!/bin/bash
folder_path="../hexagon_icons"

magick montage $folder_path/{000..024}.png ../card_shapes/{blue,green,red,yellow,purple}_shape.png $folder_path/{025..034}.png ../special_achievement_icons/{empire,monument,universe,wonder,world}.png $folder_path/{035..044}.png ../special_achievement_icons//{empire,monument,universe,wonder,world}_portrait.png -tile 15x4 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x.png
magick montage $folder_path/{045..104}.png -tile 10x6 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_10x.png
magick montage $folder_path/base_hexagon_spritesheet_{15,10}x.png -tile 1x2 -geometry +0+0 -background 'none' $folder_path/base_hexagon_spritesheet.png
rm $folder_path/base_hexagon_spritesheet_{10,15}x.png
