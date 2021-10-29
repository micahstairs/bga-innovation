#!/bin/bash

# Set up
folder_path="../card_backgrounds"
mkdir $folder_path/temp
mkdir $folder_path/temp/scaled

# BASE

empire_num=1
monument_num=2
wonder_num=3
world_num=4
universe_num=5

empire_offset="+150+180"
monument_offset="+150+200"
wonder_offset="+150+150"
world_offset="+150+160"
universe_offset="+150+190"

magick convert "$folder_path/base_special_achievement_${empire_num}.png" -crop 450x330${empire_offset} +repage  "$folder_path/temp/base_special_achievement_${empire_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${monument_num}.png" -crop 450x330${monument_offset} +repage  "$folder_path/temp/base_special_achievement_${monument_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${wonder_num}.png" -crop 450x330${wonder_offset} +repage  "$folder_path/temp/base_special_achievement_${wonder_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${world_num}.png" -crop 450x330${world_offset} +repage  "$folder_path/temp/base_special_achievement_${world_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${universe_num}.png" -crop 450x330${universe_offset} +repage  "$folder_path/temp/base_special_achievement_${universe_num}_cropped.png"

# ECHOES

destiny_num=1
heritage_num=2
history_num=3
supremacy_num=4
wealth_num=5

destiny_offset="+150+155"
heritage_offset="+102+120"
history_offset="+150+140"
supremacy_offset="+150+125"
wealth_offset="+150+170"

magick convert "$folder_path/echoes_special_achievement_${destiny_num}.png" -crop 450x330${destiny_offset} +repage  "$folder_path/temp/echoes_special_achievement_${destiny_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${heritage_num}.png" -crop 545x400${heritage_offset} +repage  -resize 450x330 "$folder_path/temp/echoes_special_achievement_${heritage_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${history_num}.png" -crop 450x330${history_offset} +repage  "$folder_path/temp/echoes_special_achievement_${history_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${supremacy_num}.png" -crop 450x330${supremacy_offset} +repage  "$folder_path/temp/echoes_special_achievement_${supremacy_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${wealth_num}.png" -crop 450x330${wealth_offset} +repage  "$folder_path/temp/echoes_special_achievement_${wealth_num}_cropped.png"

# FIGURES

advancement_num=1
expansion_num=2
rivalry_num=3
trade_num=4
war_num=5

advancement_offset="+150+110"
expansion_offset="+150+160"
rivalry_offset="+150+130"
trade_offset="+150+185"
war_offset="+150+115"

magick convert "$folder_path/figures_special_achievement_${advancement_num}.png" -crop 450x330${advancement_offset} +repage  "$folder_path/temp/figures_special_achievement_${advancement_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${expansion_num}.png" -crop 450x330${expansion_offset} +repage  "$folder_path/temp/figures_special_achievement_${expansion_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${rivalry_num}.png" -crop 450x330${rivalry_offset} +repage  "$folder_path/temp/figures_special_achievement_${rivalry_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${trade_num}.png" -crop 450x330${trade_offset} +repage  "$folder_path/temp/figures_special_achievement_${trade_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${war_num}.png" -crop 450x330${war_offset} +repage  "$folder_path/temp/figures_special_achievement_${war_num}_cropped.png"

# CITIES

fame_num=1
glory_num=2
legend_num=3
repute_num=4
victory_num=5

fame_offset="+150+120"
glory_offset="+150+170"
legend_offset="+102+100"
repute_offset="+150+140"
victory_offset="+150+135"

magick convert "$folder_path/cities_special_achievement_${fame_num}.png" -crop 450x330${fame_offset} +repage  "$folder_path/temp/cities_special_achievement_${fame_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${glory_num}.png" -crop 450x330${glory_offset} +repage  "$folder_path/temp/cities_special_achievement_${glory_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${legend_num}.png" -crop 545x400${legend_offset} +repage  -resize 450x330 "$folder_path/temp/cities_special_achievement_${legend_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${repute_num}.png" -crop 450x330${repute_offset} +repage  "$folder_path/temp/cities_special_achievement_${repute_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${victory_num}.png" -crop 450x330${victory_offset} +repage  "$folder_path/temp/cities_special_achievement_${victory_num}_cropped.png"

# Shrink images
mogrify -path $folder_path/temp/scaled -resize 150x110 \
    $folder_path/temp/base_special_achievement_{1..5}_cropped.png \
    $folder_path/temp/echoes_special_achievement_{1..5}_cropped.png \
    $folder_path/temp/figures_special_achievement_{1..5}_cropped.png \
    $folder_path/temp/cities_special_achievement_{1..5}_cropped.png

# Make landscape spritesheet
magick montage \
  $folder_path/temp/scaled/base_special_achievement_{1..5}_cropped.png \
  $folder_path/temp/scaled/echoes_special_achievement_{1..5}_cropped.png \
  $folder_path/temp/scaled/figures_special_achievement_{1..5}_cropped.png \
  $folder_path/temp/scaled/cities_special_achievement_{1..5}_cropped.png \
  -tile 5x4 -geometry +5+5 -background 'white' ../../img/special_achievements_landscape.jpg


# Rotate images to portrait
for i in {1..5}
do 
    magick convert "$folder_path/temp/scaled/base_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/temp/scaled/base_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/temp/scaled/echoes_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/temp/scaled/echoes_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/temp/scaled/figures_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/temp/scaled/figures_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/temp/scaled/cities_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/temp/scaled/cities_special_achievement_${i}_cropped_rotated.png"
done

# Make portrait spritesheet
magick montage \
  $folder_path/temp/scaled/base_special_achievement_{1..5}_cropped_rotated.png \
  $folder_path/temp/scaled/echoes_special_achievement_{1..5}_cropped_rotated.png \
  $folder_path/temp/scaled/figures_special_achievement_{1..5}_cropped_rotated.png \
  $folder_path/temp/scaled/cities_special_achievement_{1..5}_cropped_rotated.png \
  -tile 5x4 -geometry +5+5 -background 'white' ../../img/special_achievements_portrait.jpg

# Clean up
rm -r $folder_path/temp
