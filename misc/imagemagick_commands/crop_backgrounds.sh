#!/bin/bash

background_path="../card_backgrounds"
colorblind_path="../card_shapes"
colorblind_offset="+580+12" #offset from top-left corner of image

# (1) Generate colored square borders set on white background
function crop_add_colorblind()
{
    local border_color=${1}  # e.g. blue
    local colorblind_img="${colorblind_path}/${border_color}_shape.png"
    local background_img="${background_path}/${border_color}.png" 
    local result_img="${background_path}/${border_color}_cropped.png"


    magick composite -geometry ${colorblind_offset} \
        ${colorblind_img} \
        "${background_img}[666x475+42+36]" \
        ${result_img}

}

for color in {blue,red,green,yellow,purple}; do
    crop_add_colorblind ${color}
done