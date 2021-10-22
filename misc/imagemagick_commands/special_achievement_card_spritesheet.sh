#!/bin/bash
folder_path="../card_backgrounds"

empire_num=1
monument_num=2
wonder_num=3
world_num=4
universe_num=5

empire_offset="+145+190"
monument_offset="+145+210"
wonder_offset="+150+150"
world_offset="+145+160"
universe_offset="+145+190"

#crop, make cropped spritesheet
magick convert "$folder_path/special_achievement_${empire_num}.png" -crop 450x330${empire_offset} +repage  "$folder_path/special_achievement_${empire_num}_cropped.png"
magick convert "$folder_path/special_achievement_${monument_num}.png" -crop 450x330${monument_offset} +repage  "$folder_path/special_achievement_${monument_num}_cropped.png"
magick convert "$folder_path/special_achievement_${wonder_num}.png" -crop 450x330${wonder_offset} +repage  "$folder_path/special_achievement_${wonder_num}_cropped.png"
magick convert "$folder_path/special_achievement_${world_num}.png" -crop 450x330${world_offset} +repage  "$folder_path/special_achievement_${world_num}_cropped.png"
magick convert "$folder_path/special_achievement_${universe_num}.png" -crop 450x330${universe_offset} +repage  "$folder_path/special_achievement_${universe_num}_cropped.png"
magick convert "$folder_path/special_achievement_${universe_num}.png" -crop 450x330${universe_offset} +repage  "$folder_path/special_achievement_${universe_num}_cropped.png"
magick convert "$folder_path/action_1_text_removed.png" -resize 450x330 "$folder_path/action_1_text_removed_resize.png"
magick montage $folder_path/special_achievement_{1..5}_cropped.png $folder_path/action_1_text_removed_resize.png -tile 6x1 -geometry +5+5 -background 'white' $folder_path/special_achievement_card_spritesheet.png

#rotate, make rotated spritesheet
for i in {1..5}
do 
    magick convert "$folder_path/special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/special_achievement_${i}_cropped_rotated.png"
done

magick montage $folder_path/special_achievement_{1..5}_cropped_rotated.png  -tile 5x1 -geometry +5+5 -background 'white' $folder_path/special_achievement_card_rotated_spritesheet.png
rm $folder_path/action_1_text_removed_resize.png
