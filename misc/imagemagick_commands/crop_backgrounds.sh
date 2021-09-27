#!/bin/bash

folder_path="../card_backgrounds"

magick convert -crop 666x476+42+36 $folder_path/blue.png  $folder_path/blue_cropped.png
magick convert -crop 666x476+42+36 $folder_path/red.png  $folder_path/red_cropped.png
magick convert -crop 666x476+42+36 $folder_path/yellow.png  $folder_path/yellow_cropped.png
magick convert -crop 666x476+42+36 $folder_path/green.png  $folder_path/green_cropped.png
magick convert -crop 666x476+42+36 $folder_path/purple.png  $folder_path/purple_cropped.png
