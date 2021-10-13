#!/bin/bash
folder_path="../card_backgrounds"

magick montage $folder_path/age_{01..10}_base.png $folder_path/age_{01..10}_echoes.png $folder_path/age_{01..10}_figures.png $folder_path/age_{01..10}_cities.png $folder_path/age_{01..10}_artifacts.png -tile 10x5 -geometry +5+5 -background 'none' $folder_path/card_background_portrait_spritesheet.png
