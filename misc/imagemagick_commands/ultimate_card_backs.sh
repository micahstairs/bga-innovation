#!/bin/bash
mkdir temp

mogrify -path temp -resize 110x150 \
    ../ultimate/CardBacks/InnoUlt_CardBacks_PRODUCTION{1..11}.png \
    ../ultimate/CardBacks/InnoUlt_CardBacks_PRODUCTION{13..67}.png

magick montage \
    temp/InnoUlt_CardBacks_PRODUCTION{1..11}.png \
    temp/InnoUlt_CardBacks_PRODUCTION{13..67}.png \
    -tile 11x6 -geometry +3+3 -background 'none' ../../img/ultimate_card_backs_portrait.jpg

rm -r temp
