#!/bin/bash

# File paths/prefixes
ICON_SRC_PATH="../square_icons"
BORDER_SRC_PATH="../card_icon_borders"
RESULT_PATH="../square_icons/bordered"

function borderize()
{
    local icon_type=${1}  # e.g. crown leaf lightbulb castle factory clock
    local border_color=${2}  # e.g. blue red green yellow purple
    local icon_img="${ICON_SRC_PATH}/${icon_type}.png"
    local border_img="${BORDER_SRC_PATH}/${border_color}_square_icon_border.png"
    local result_img="${RESULT_PATH}/${icon_type}_${border_color}.png"
    # -1-1: there are subpixel problems with rendering artifacts => shift centered content by 1px towards top+left
    magick convert -composite -gravity center -geometry -1-1 $border_img $icon_img $result_img
}

for icon in {crown,leaf,lightbulb,castle,factory,clock}; do
    for color in {blue,red,green,yellow,purple}; do
        borderize ${icon} ${color}
    done
done

magick montage \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_blue.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_red.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_green.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_yellow.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_purple.png" \
-tile 6x5 -geometry +2+2 -background 'none' "${RESULT_PATH}/bordered_square_icons_spritesheet.png"

