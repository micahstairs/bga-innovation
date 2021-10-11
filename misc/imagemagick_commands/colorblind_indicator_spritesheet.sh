#!/bin/bash

folder_path="../card_shapes"
magick montage $folder_path/{blue,green,red,yellow,purple}_shape.png -trim -tile 5x1 -geometry 120x120+5+5 -background 'none' $folder_path/colorblind_indicator_spritesheet.png