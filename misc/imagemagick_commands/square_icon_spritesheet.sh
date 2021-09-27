#!/bin/bash

folder_path="../square_icons"
magick montage $folder_path/{crown,leaf,lightbulb,castle,factory,clock}.png -tile 6x1 -geometry +5+5 -background 'white' $folder_path/square_icon_spritesheet.png