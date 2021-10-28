#!/bin/bash
folder_path="../card_backgrounds"

#base
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

magick convert "$folder_path/base_special_achievement_${empire_num}.png" -crop 450x330${empire_offset} +repage  "$folder_path/base_special_achievement_${empire_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${monument_num}.png" -crop 450x330${monument_offset} +repage  "$folder_path/base_special_achievement_${monument_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${wonder_num}.png" -crop 450x330${wonder_offset} +repage  "$folder_path/base_special_achievement_${wonder_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${world_num}.png" -crop 450x330${world_offset} +repage  "$folder_path/base_special_achievement_${world_num}_cropped.png"
magick convert "$folder_path/base_special_achievement_${universe_num}.png" -crop 450x330${universe_offset} +repage  "$folder_path/base_special_achievement_${universe_num}_cropped.png"

#echoes
destiny_num=1
heritage_num=2
history_num=3
supremacy_num=4
wealth_num=5

destiny_offset="+145+190"
heritage_offset="+145+210"
history_offset="+150+150"
supremacy_offset="+145+160"
wealth_offset="+145+190"

magick convert "$folder_path/echoes_special_achievement_${destiny_num}.png" -crop 450x330${destiny_offset} +repage  "$folder_path/echoes_special_achievement_${destiny_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${heritage_num}.png" -crop 450x330${heritage_offset} +repage  "$folder_path/echoes_special_achievement_${heritage_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${history_num}.png" -crop 450x330${history_offset} +repage  "$folder_path/echoes_special_achievement_${history_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${supremacy_num}.png" -crop 450x330${supremacy_offset} +repage  "$folder_path/echoes_special_achievement_${supremacy_num}_cropped.png"
magick convert "$folder_path/echoes_special_achievement_${wealth_num}.png" -crop 450x330${wealth_offset} +repage  "$folder_path/echoes_special_achievement_${wealth_num}_cropped.png"

#figures
advancement_num=1
expansion_num=2
rivalry_num=3
trade_num=4
war_num=5

advancement_offset="+145+190"
expansion_offset="+145+210"
rivalry_offset="+150+150"
trade_offset="+145+160"
war_offset="+145+190"

magick convert "$folder_path/figures_special_achievement_${advancement_num}.png" -crop 450x330${advancement_offset} +repage  "$folder_path/figures_special_achievement_${advancement_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${expansion_num}.png" -crop 450x330${expansion_offset} +repage  "$folder_path/figures_special_achievement_${expansion_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${rivalry_num}.png" -crop 450x330${rivalry_offset} +repage  "$folder_path/figures_special_achievement_${rivalry_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${trade_num}.png" -crop 450x330${trade_offset} +repage  "$folder_path/figures_special_achievement_${trade_num}_cropped.png"
magick convert "$folder_path/figures_special_achievement_${war_num}.png" -crop 450x330${war_offset} +repage  "$folder_path/figures_special_achievement_${war_num}_cropped.png"

#cities
fame_num=1
glory_num=2
legend_num=3
repute_num=4
victory_num=5

fame_offset="+145+190"
glory_offset="+145+210"
legend_offset="+150+150"
repute_offset="+145+160"
victory_offset="+145+190"

magick convert "$folder_path/cities_special_achievement_${fame_num}.png" -crop 450x330${fame_offset} +repage  "$folder_path/cities_special_achievement_${fame_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${glory_num}.png" -crop 450x330${glory_offset} +repage  "$folder_path/cities_special_achievement_${glory_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${legend_num}.png" -crop 450x330${legend_offset} +repage  "$folder_path/cities_special_achievement_${legend_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${repute_num}.png" -crop 450x330${repute_offset} +repage  "$folder_path/cities_special_achievement_${repute_num}_cropped.png"
magick convert "$folder_path/cities_special_achievement_${victory_num}.png" -crop 450x330${victory_offset} +repage  "$folder_path/cities_special_achievement_${victory_num}_cropped.png"


#make cropped spritesheet
magick montage $folder_path/base_special_achievement_{1..5}_cropped.png  $folder_path/echoes_special_achievement_{1..5}_cropped.png $folder_path/figures_special_achievement_{1..5}_cropped.png $folder_path/cities_special_achievement_{1..5}_cropped.png -tile 5x4 -geometry +5+5 -background 'white' $folder_path/special_achievement_card_spritesheet.png


#rotate, make rotated spritesheet
for i in {1..5}
do 
    magick convert "$folder_path/base_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/base_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/echoes_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/echoes_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/figures_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/figures_special_achievement_${i}_cropped_rotated.png"
    magick convert "$folder_path/cities_special_achievement_${i}_cropped.png" -rotate 90 "$folder_path/cities_special_achievement_${i}_cropped_rotated.png"

done

magick montage $folder_path/base_special_achievement_{1..5}_cropped_rotated.png  $folder_path/echoes_special_achievement_{1..5}_cropped_rotated.png $folder_path/figures_special_achievement_{1..5}_cropped_rotated.png $folder_path/cities_special_achievement_{1..5}_cropped_rotated.png -tile 5x4 -geometry +5+5 -background 'white' $folder_path/special_achievement_card_rotated_spritesheet.png
rm $folder_path/base_special_achievement_{1..5}_cropped_rotated.png  $folder_path/echoes_special_achievement_{1..5}_cropped_rotated.png $folder_path/figures_special_achievement_{1..5}_cropped_rotated.png $folder_path/cities_special_achievement_{1..5}_cropped_rotated.png 
rm $folder_path/base_special_achievement_{1..5}_cropped.png  $folder_path/echoes_special_achievement_{1..5}_cropped.png $folder_path/figures_special_achievement_{1..5}_cropped.png
