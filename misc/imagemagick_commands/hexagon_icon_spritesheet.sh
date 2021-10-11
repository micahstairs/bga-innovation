#!/bin/bash
folder_path="../hexagon_icons"

magick montage $folder_path/{000..014}.png -trim -tile 15x1 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x_1.png

magick montage $folder_path/{015..024}.png -trim -tile 10x1 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x_2.png


# customized sizes for just these icons
magick montage $folder_path/base_hexagon_spritesheet_15x_2.png \( ../card_shapes/blue_shape.png -resize 100x120 \) \( ../card_shapes/red_shape.png -resize 130x130 \) \( ../card_shapes/green_shape.png -resize 120x120 \) \( ../card_shapes/yellow_shape.png -resize 120x120 \) \( ../card_shapes/purple_shape.png -resize 130x130 \) -trim -tile 6x1 -geometry +13+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x_2.png


magick montage $folder_path/{025..034}.png ../special_achievement_icons/{empire,monument,universe,wonder,world}.png -trim -tile 15x1 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x_3.png
magick montage $folder_path/{035..044}.png ../special_achievement_icons/{empire,monument,universe,wonder,world}_portrait.png -trim -tile 15x1 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_15x_4.png

magick montage $folder_path/{045..104}.png -trim -tile 10x6 -geometry 120x120+5+5 -background 'none' $folder_path/base_hexagon_spritesheet_10x.png
magick montage $folder_path/base_hexagon_spritesheet_15x_{1..4}.png $folder_path/base_hexagon_spritesheet_10x.png -tile 1x5 -geometry +0+0 -background 'none' $folder_path/base_hexagon_spritesheet.png
rm $folder_path/base_hexagon_spritesheet_15x_{1..4}.png $folder_path/base_hexagon_spritesheet_10x.png
