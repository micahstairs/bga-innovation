#!/bin/bash

# Coordinates for hexagons
TOP_LEFT="97,58 60,122 97,186 170,186 207,122 170,58"
BOTTOM_LEFT="99,351 62,415 99,479 172,479 209,415 172,351"
BOTTOM_CENTER="341,351 304,415 341,479 414,479 451,415 414,351"
BOTTOM_RIGHT="573,349 536,413 573,477 646,477 683,413 646,349"

# Hexagon icon borders
RED_BORDER="../card_icon_borders/red_hexagon_icon_border.png"
YELLOW_BORDER="../card_icon_borders/yellow_hexagon_icon_border.png"
BLUE_BORDER="../card_icon_borders/blue_hexagon_icon_border.png"
GREEN_BORDER="../card_icon_borders/green_hexagon_icon_border.png"
PURPLE_BORDER="../card_icon_borders/purple_hexagon_icon_border.png"

# File paths/prefixes
SAVE_PATH="../hexagon_icons"
READ_PATH="../cards/Print_BaseCards_front"


# Extracts hexagon from card and places in matching border
# arg1: filename of card to extract from
# arg2: position of hexagon in card
# arg3: path for border file
# arg4: name of png to save to
# example: make_hexagon "Print_BaseCards_front-027.png" "$TOP_LEFT" "$BLUE_BORDER" "000.png"
make_hexagon()
{
    magick "${READ_PATH}/$1" \( +clone -fill Black -colorize 100 -fill White -draw "polygon ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "${SAVE_PATH}/$4"
    magick convert -gravity center $3 "${SAVE_PATH}/$4" -composite "${SAVE_PATH}/$4"
}

# Age 1
make_hexagon "Print_BaseCards_front-010.png" "$TOP_LEFT"       "$BLUE_BORDER"   "000.png"
make_hexagon "Print_BaseCards_front-011.png" "$TOP_LEFT"       "$BLUE_BORDER"   "001.png"
make_hexagon "Print_BaseCards_front-012.png" "$TOP_LEFT"       "$BLUE_BORDER"   "002.png"
make_hexagon "Print_BaseCards_front-001.png" "$BOTTOM_CENTER"  "$RED_BORDER"    "003.png"
make_hexagon "Print_BaseCards_front-002.png" "$BOTTOM_CENTER"  "$RED_BORDER"    "004.png"
make_hexagon "Print_BaseCards_front-003.png" "$BOTTOM_CENTER"  "$RED_BORDER"    "005.png"
make_hexagon "Print_BaseCards_front-007.png" "$TOP_LEFT"       "$GREEN_BORDER"  "006.png"
make_hexagon "Print_BaseCards_front-008.png" "$BOTTOM_CENTER"  "$GREEN_BORDER"  "007.png"
make_hexagon "Print_BaseCards_front-009.png" "$TOP_LEFT"       "$GREEN_BORDER"  "008.png"
make_hexagon "Print_BaseCards_front-004.png" "$TOP_LEFT"       "$YELLOW_BORDER" "009.png"
make_hexagon "Print_BaseCards_front-005.png" "$BOTTOM_CENTER"  "$YELLOW_BORDER" "010.png"
make_hexagon "Print_BaseCards_front-006.png" "$BOTTOM_LEFT"    "$YELLOW_BORDER" "011.png"
make_hexagon "Print_BaseCards_front-013.png" "$TOP_LEFT"       "$PURPLE_BORDER" "012.png"
make_hexagon "Print_BaseCards_front-014.png" "$TOP_LEFT"       "$PURPLE_BORDER" "013.png"
make_hexagon "Print_BaseCards_front-015.png" "$TOP_LEFT"       "$PURPLE_BORDER" "014.png"

# Age 2
make_hexagon	"Print_BaseCards_front-022.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"015.png"
make_hexagon	"Print_BaseCards_front-023.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"016.png"
make_hexagon	"Print_BaseCards_front-016.png"	"$BOTTOM_LEFT"	"$RED_BORDER"	"017.png"
make_hexagon	"Print_BaseCards_front-017.png"	"$BOTTOM_CENTER"	"$RED_BORDER"	"018.png"
make_hexagon	"Print_BaseCards_front-020.png"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"019.png"
make_hexagon	"Print_BaseCards_front-021.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"020.png"
make_hexagon	"Print_BaseCards_front-018.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"021.png"
make_hexagon	"Print_BaseCards_front-019.png"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"022.png"
make_hexagon	"Print_BaseCards_front-024.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"023.png"
make_hexagon	"Print_BaseCards_front-025.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"024.png"

# Age 3
make_hexagon	"Print_BaseCards_front-032.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"025.png"
make_hexagon	"Print_BaseCards_front-033.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"026.png"
make_hexagon	"Print_BaseCards_front-026.png"	"$BOTTOM_LEFT"	"$RED_BORDER"	"027.png"
make_hexagon	"Print_BaseCards_front-027.png"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"028.png"
make_hexagon	"Print_BaseCards_front-030.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"029.png"
make_hexagon	"Print_BaseCards_front-031.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"030.png"
make_hexagon	"Print_BaseCards_front-028.png"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"031.png"
make_hexagon	"Print_BaseCards_front-029.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"032.png"
make_hexagon	"Print_BaseCards_front-034.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"033.png"
make_hexagon	"Print_BaseCards_front-035.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"034.png"

# Age 4
make_hexagon	"Print_BaseCards_front-042.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"035.png"
make_hexagon	"Print_BaseCards_front-043.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"036.png"
make_hexagon	"Print_BaseCards_front-036.png"	"$TOP_LEFT"	"$RED_BORDER"	"037.png"
make_hexagon	"Print_BaseCards_front-037.png"	"$TOP_LEFT"	"$RED_BORDER"	"038.png"
make_hexagon	"Print_BaseCards_front-040.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"039.png"
make_hexagon	"Print_BaseCards_front-041.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"040.png"
make_hexagon	"Print_BaseCards_front-038.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"041.png"
make_hexagon	"Print_BaseCards_front-039.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"042.png"
make_hexagon	"Print_BaseCards_front-044.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"043.png"
make_hexagon	"Print_BaseCards_front-045.png"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"044.png"

# Age 5
make_hexagon	"Print_BaseCards_front-052.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"045.png"
make_hexagon	"Print_BaseCards_front-053.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"046.png"
make_hexagon	"Print_BaseCards_front-046.png"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"047.png"
make_hexagon	"Print_BaseCards_front-047.png"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"048.png"
make_hexagon	"Print_BaseCards_front-050.png"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"049.png"
make_hexagon	"Print_BaseCards_front-051.png"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"050.png"
make_hexagon	"Print_BaseCards_front-048.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"051.png"
make_hexagon	"Print_BaseCards_front-049.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"052.png"
make_hexagon	"Print_BaseCards_front-054.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"053.png"
make_hexagon	"Print_BaseCards_front-055.png"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"054.png"

# Age 6
make_hexagon	"Print_BaseCards_front-062.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"055.png"
make_hexagon	"Print_BaseCards_front-063.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"056.png"
make_hexagon	"Print_BaseCards_front-056.png"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"057.png"
make_hexagon	"Print_BaseCards_front-057.png"	"$BOTTOM_CENTER"	"$RED_BORDER"	"058.png"
make_hexagon	"Print_BaseCards_front-060.png"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"059.png"
make_hexagon	"Print_BaseCards_front-061.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"060.png"
make_hexagon	"Print_BaseCards_front-058.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"061.png"
make_hexagon	"Print_BaseCards_front-059.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"062.png"
make_hexagon	"Print_BaseCards_front-064.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"063.png"
make_hexagon	"Print_BaseCards_front-065.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"064.png"

# Age 7
make_hexagon	"Print_BaseCards_front-072.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"065.png"
make_hexagon	"Print_BaseCards_front-073.png"	"$TOP_LEFT"	"$BLUE_BORDER"	"066.png"
make_hexagon	"Print_BaseCards_front-066.png"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"067.png"
make_hexagon	"Print_BaseCards_front-067.png"	"$TOP_LEFT"	"$RED_BORDER"	"068.png"
make_hexagon	"Print_BaseCards_front-070.png"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"069.png"
make_hexagon	"Print_BaseCards_front-071.png"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"070.png"
make_hexagon	"Print_BaseCards_front-068.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"071.png"
make_hexagon	"Print_BaseCards_front-069.png"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"072.png"
make_hexagon	"Print_BaseCards_front-074.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"073.png"
make_hexagon	"Print_BaseCards_front-075.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"074.png"

# Age 8
make_hexagon	"Print_BaseCards_front-082.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"075.png"
make_hexagon	"Print_BaseCards_front-083.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"076.png"
make_hexagon	"Print_BaseCards_front-076.png"	"$BOTTOM_LEFT"	"$RED_BORDER"	"077.png"
make_hexagon	"Print_BaseCards_front-077.png"	"$TOP_LEFT"	"$RED_BORDER"	"078.png"
make_hexagon	"Print_BaseCards_front-080.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"079.png"
make_hexagon	"Print_BaseCards_front-081.png"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"080.png"
make_hexagon	"Print_BaseCards_front-078.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"081.png"
make_hexagon	"Print_BaseCards_front-079.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"082.png"
make_hexagon	"Print_BaseCards_front-084.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"083.png"
make_hexagon	"Print_BaseCards_front-085.png"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"084.png"

# Age 9
make_hexagon	"Print_BaseCards_front-092.png"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"085.png"
make_hexagon	"Print_BaseCards_front-093.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"086.png"
make_hexagon	"Print_BaseCards_front-086.png"	"$BOTTOM_CENTER"	"$RED_BORDER"	"087.png"
make_hexagon	"Print_BaseCards_front-087.png"	"$TOP_LEFT"	"$RED_BORDER"	"088.png"
make_hexagon	"Print_BaseCards_front-090.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"089.png"
make_hexagon	"Print_BaseCards_front-091.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"090.png"
make_hexagon	"Print_BaseCards_front-088.png"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"091.png"
make_hexagon	"Print_BaseCards_front-089.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"092.png"
make_hexagon	"Print_BaseCards_front-094.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"093.png"
make_hexagon	"Print_BaseCards_front-095.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"094.png"

# Age 10
make_hexagon	"Print_BaseCards_front-102.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"095.png"
make_hexagon	"Print_BaseCards_front-103.png"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"096.png"
make_hexagon	"Print_BaseCards_front-096.png"	"$TOP_LEFT"	"$RED_BORDER"	"097.png"
make_hexagon	"Print_BaseCards_front-097.png"	"$TOP_LEFT"	"$RED_BORDER"	"098.png"
make_hexagon	"Print_BaseCards_front-100.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"099.png"
make_hexagon	"Print_BaseCards_front-101.png"	"$TOP_LEFT"	"$GREEN_BORDER"	"100.png"
make_hexagon	"Print_BaseCards_front-098.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"101.png"
make_hexagon	"Print_BaseCards_front-099.png"	"$TOP_LEFT"	"$YELLOW_BORDER"	"102.png"
make_hexagon	"Print_BaseCards_front-104.png"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"103.png"
make_hexagon	"Print_BaseCards_front-105.png"	"$TOP_LEFT"	"$PURPLE_BORDER"	"104.png"
