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
make_hexagon "Print_BaseCards_front-012.png" "$TOP_LEFT"       "$BLUE_BORDER"   "001.png"
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
# ...
