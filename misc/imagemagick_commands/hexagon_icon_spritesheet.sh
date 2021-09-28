#!/bin/bash
folder_path="../hexagon_icons"

magick montage $folder_path/{000..014}.png -tile 15x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_1.png
magick montage $folder_path/{015..024}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_2.png
magick montage $folder_path/{025..034}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_3.png
magick montage $folder_path/{035..044}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_4.png
magick montage $folder_path/{045..054}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_5.png
magick montage $folder_path/{055..064}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_6.png
magick montage $folder_path/{065..074}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_7.png
magick montage $folder_path/{075..084}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_8.png
magick montage $folder_path/{085..094}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_9.png
magick montage $folder_path/{095..104}.png -tile 10x1 -geometry +5+5 -background 'white' $folder_path/base_hexagon_spritesheet_age_10.png
