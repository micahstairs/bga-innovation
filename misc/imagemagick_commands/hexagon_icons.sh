#!/bin/bash

# Setup
mkdir temp
### BASE ###

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
READ_PATH="../cards/Print_BaseCards_front/Print_BaseCards_front-"
FILE_SUFFIX="_base.png"

# Tints a color
function tint_image() {
    local img_path=${1}
    local tint_color=${2} # e.g. green
    local tint_intensity=${3} # out of 200
    magick $img_path -fill $tint_color -tint $tint_intensity $img_path
}

# Extracts hexagon from a base card and places in a colored border
# arg1: input filename
# arg2: position of hexagon in card
# arg3: name of border image
# arg4: output filename
# example: extract_hexagon "010" "$TOP_LEFT" "$BLUE_BORDER" "000"
extract_hexagon()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "polygon ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$4${FILE_SUFFIX}"
    magick convert -gravity center $3 "temp/$4${FILE_SUFFIX}" -composite "temp/$4${FILE_SUFFIX}"
    
    #tint only green hexes
    if [[ "${3}" == "${GREEN_BORDER}" ]]; then
        tint_image "temp/$4${FILE_SUFFIX}" green 30
    fi
    
}

echo "Extracting Base hexagon icons..."

# Age 1
extract_hexagon "010" "$TOP_LEFT"       "$BLUE_BORDER"   "000"
extract_hexagon "011" "$TOP_LEFT"       "$BLUE_BORDER"   "001"
extract_hexagon "012" "$TOP_LEFT"       "$BLUE_BORDER"   "002"
extract_hexagon "001" "$BOTTOM_CENTER"  "$RED_BORDER"    "003"
extract_hexagon "002" "$BOTTOM_CENTER"  "$RED_BORDER"    "004"
extract_hexagon "003" "$BOTTOM_CENTER"  "$RED_BORDER"    "005"
extract_hexagon "007" "$TOP_LEFT"       "$GREEN_BORDER"  "006"
extract_hexagon "008" "$BOTTOM_CENTER"  "$GREEN_BORDER"  "007"
extract_hexagon "009" "$TOP_LEFT"       "$GREEN_BORDER"  "008"
extract_hexagon "004" "$TOP_LEFT"       "$YELLOW_BORDER" "009"
extract_hexagon "005" "$BOTTOM_CENTER"  "$YELLOW_BORDER" "010"
extract_hexagon "006" "$BOTTOM_LEFT"    "$YELLOW_BORDER" "011"
extract_hexagon "013" "$TOP_LEFT"       "$PURPLE_BORDER" "012"
extract_hexagon "014" "$TOP_LEFT"       "$PURPLE_BORDER" "013"
extract_hexagon "015" "$TOP_LEFT"       "$PURPLE_BORDER" "014"

# Age 2
extract_hexagon	"022"	"$TOP_LEFT"	"$BLUE_BORDER"	"015"
extract_hexagon	"023"	"$TOP_LEFT"	"$BLUE_BORDER"	"016"
extract_hexagon	"016"	"$BOTTOM_LEFT"	"$RED_BORDER"	"017"
extract_hexagon	"017"	"$BOTTOM_CENTER"	"$RED_BORDER"	"018"
extract_hexagon	"020"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"019"
extract_hexagon	"021"	"$TOP_LEFT"	"$GREEN_BORDER"	"020"
extract_hexagon	"018"	"$TOP_LEFT"	"$YELLOW_BORDER"	"021"
extract_hexagon	"019"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"022"
extract_hexagon	"024"	"$TOP_LEFT"	"$PURPLE_BORDER"	"023"
extract_hexagon	"025"	"$TOP_LEFT"	"$PURPLE_BORDER"	"024"

# Age 3
extract_hexagon	"032"	"$TOP_LEFT"	"$BLUE_BORDER"	"025"
extract_hexagon	"033"	"$TOP_LEFT"	"$BLUE_BORDER"	"026"
extract_hexagon	"026"	"$BOTTOM_LEFT"	"$RED_BORDER"	"027"
extract_hexagon	"027"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"028"
extract_hexagon	"030"	"$TOP_LEFT"	"$GREEN_BORDER"	"029"
extract_hexagon	"031"	"$TOP_LEFT"	"$GREEN_BORDER"	"030"
extract_hexagon	"028"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"031"
extract_hexagon	"029"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"032"
extract_hexagon	"034"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"033"
extract_hexagon	"035"	"$TOP_LEFT"	"$PURPLE_BORDER"	"034"

# Age 4
extract_hexagon	"042"	"$TOP_LEFT"	"$BLUE_BORDER"	"035"
extract_hexagon	"043"	"$TOP_LEFT"	"$BLUE_BORDER"	"036"
extract_hexagon	"036"	"$TOP_LEFT"	"$RED_BORDER"	"037"
extract_hexagon	"037"	"$TOP_LEFT"	"$RED_BORDER"	"038"
extract_hexagon	"040"	"$TOP_LEFT"	"$GREEN_BORDER"	"039"
extract_hexagon	"041"	"$TOP_LEFT"	"$GREEN_BORDER"	"040"
extract_hexagon	"038"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"041"
extract_hexagon	"039"	"$TOP_LEFT"	"$YELLOW_BORDER"	"042"
extract_hexagon	"044"	"$TOP_LEFT"	"$PURPLE_BORDER"	"043"
extract_hexagon	"045"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"044"

# Age 5
extract_hexagon	"052"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"045"
extract_hexagon	"053"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"046"
extract_hexagon	"046"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"047"
extract_hexagon	"047"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"048"
extract_hexagon	"050"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"049"
extract_hexagon	"051"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"050"
extract_hexagon	"048"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"051"
extract_hexagon	"049"	"$TOP_LEFT"	"$YELLOW_BORDER"	"052"
extract_hexagon	"054"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"053"
extract_hexagon	"055"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"054"

# Age 6
extract_hexagon	"062"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"055"
extract_hexagon	"063"	"$TOP_LEFT"	"$BLUE_BORDER"	"056"
extract_hexagon	"056"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"057"
extract_hexagon	"057"	"$BOTTOM_CENTER"	"$RED_BORDER"	"058"
extract_hexagon	"060"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"059"
extract_hexagon	"061"	"$TOP_LEFT"	"$GREEN_BORDER"	"060"
extract_hexagon	"058"	"$TOP_LEFT"	"$YELLOW_BORDER"	"061"
extract_hexagon	"059"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"062"
extract_hexagon	"064"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"063"
extract_hexagon	"065"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"064"

# Age 7
extract_hexagon	"072"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"065"
extract_hexagon	"073"	"$TOP_LEFT"	"$BLUE_BORDER"	"066"
extract_hexagon	"066"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"067"
extract_hexagon	"067"	"$TOP_LEFT"	"$RED_BORDER"	"068"
extract_hexagon	"070"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"069"
extract_hexagon	"071"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"070"
extract_hexagon	"068"	"$TOP_LEFT"	"$YELLOW_BORDER"	"071"
extract_hexagon	"069"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"072"
extract_hexagon	"074"	"$TOP_LEFT"	"$PURPLE_BORDER"	"073"
extract_hexagon	"075"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"074"

# Age 8
extract_hexagon	"082"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"075"
extract_hexagon	"083"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"076"
extract_hexagon	"076"	"$BOTTOM_LEFT"	"$RED_BORDER"	"077"
extract_hexagon	"077"	"$TOP_LEFT"	"$RED_BORDER"	"078"
extract_hexagon	"080"	"$TOP_LEFT"	"$GREEN_BORDER"	"079"
extract_hexagon	"081"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"080"
extract_hexagon	"078"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"081"
extract_hexagon	"079"	"$TOP_LEFT"	"$YELLOW_BORDER"	"082"
extract_hexagon	"084"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"083"
extract_hexagon	"085"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"084"

# Age 9
extract_hexagon	"092"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"085"
extract_hexagon	"093"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"086"
extract_hexagon	"086"	"$BOTTOM_CENTER"	"$RED_BORDER"	"087"
extract_hexagon	"087"	"$TOP_LEFT"	"$RED_BORDER"	"088"
extract_hexagon	"090"	"$TOP_LEFT"	"$GREEN_BORDER"	"089"
extract_hexagon	"091"	"$TOP_LEFT"	"$GREEN_BORDER"	"090"
extract_hexagon	"088"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"091"
extract_hexagon	"089"	"$TOP_LEFT"	"$YELLOW_BORDER"	"092"
extract_hexagon	"094"	"$TOP_LEFT"	"$PURPLE_BORDER"	"093"
extract_hexagon	"095"	"$TOP_LEFT"	"$PURPLE_BORDER"	"094"

# Age 10
extract_hexagon	"102"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"095"
extract_hexagon	"103"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"096"
extract_hexagon	"096"	"$TOP_LEFT"	"$RED_BORDER"	"097"
extract_hexagon	"097"	"$TOP_LEFT"	"$RED_BORDER"	"098"
extract_hexagon	"100"	"$TOP_LEFT"	"$GREEN_BORDER"	"099"
extract_hexagon	"101"	"$TOP_LEFT"	"$GREEN_BORDER"	"100"
extract_hexagon	"098"	"$TOP_LEFT"	"$YELLOW_BORDER"	"101"
extract_hexagon	"099"	"$TOP_LEFT"	"$YELLOW_BORDER"	"102"
extract_hexagon	"104"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"103"
extract_hexagon	"105"	"$TOP_LEFT"	"$PURPLE_BORDER"	"104"

# ### ARTIFACTS ###

# The bottom right icon is a few pixels off of the base set.
BOTTOM_RIGHT="576,349 539,413 576,477 649,477 686,413 649,349"
# Timbuktu has an icon on the top-right
TOP_RIGHT="577,73 540,138 577,202 651,202 688,138 651,73"

# File paths/prefixes
READ_PATH="../cards/Print_ArtifactsCards_front/Print_ArtifactsCards_front-"
FILE_SUFFIX="_artifacts.png"

echo "Extracting Artifact hexagon icons..."

# Age 1
extract_hexagon "010" "$BOTTOM_CENTER"       "$BLUE_BORDER"   "000"
extract_hexagon "011" "$BOTTOM_RIGHT"       "$BLUE_BORDER"   "001"
extract_hexagon "012" "$BOTTOM_LEFT"       "$BLUE_BORDER"   "002"
extract_hexagon "001" "$BOTTOM_CENTER"  "$RED_BORDER"    "003"
extract_hexagon "002" "$BOTTOM_RIGHT"  "$RED_BORDER"    "004"
extract_hexagon "003" "$TOP_LEFT"  "$RED_BORDER"    "005"
extract_hexagon "007" "$BOTTOM_LEFT"       "$GREEN_BORDER"  "006"
extract_hexagon "008" "$BOTTOM_LEFT"  "$GREEN_BORDER"  "007"
extract_hexagon "009" "$TOP_LEFT"       "$GREEN_BORDER"  "008"
extract_hexagon "004" "$TOP_LEFT"       "$YELLOW_BORDER" "009"
extract_hexagon "005" "$TOP_LEFT"  "$YELLOW_BORDER" "010"
extract_hexagon "006" "$TOP_LEFT"    "$YELLOW_BORDER" "011"
extract_hexagon "013" "$BOTTOM_CENTER"       "$PURPLE_BORDER" "012"
extract_hexagon "014" "$TOP_LEFT"       "$PURPLE_BORDER" "013"
extract_hexagon "015" "$TOP_LEFT"       "$PURPLE_BORDER" "014"

# Age 2
extract_hexagon	"022"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"015"
extract_hexagon	"023"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"016"
extract_hexagon	"016"	"$BOTTOM_LEFT"	"$RED_BORDER"	"017"
extract_hexagon	"017"	"$TOP_LEFT"	"$RED_BORDER"	"018"
extract_hexagon	"020"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"019"
extract_hexagon	"021"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"020"
extract_hexagon	"018"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"021"
extract_hexagon	"019"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"022"
extract_hexagon	"024"	"$TOP_LEFT"	"$PURPLE_BORDER"	"023"
extract_hexagon	"025"	"$TOP_LEFT"	"$PURPLE_BORDER"	"024"

# Age 3
extract_hexagon	"032"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"025"
extract_hexagon	"033"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"026"
extract_hexagon	"026"	"$BOTTOM_LEFT"	"$RED_BORDER"	"027"
extract_hexagon	"027"	"$TOP_LEFT"	"$RED_BORDER"	"028"
extract_hexagon	"030"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"029"
extract_hexagon	"031"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"030"
extract_hexagon	"028"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"031"
extract_hexagon	"029"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"032"
extract_hexagon	"034"	"$TOP_LEFT"	"$PURPLE_BORDER"	"033"
extract_hexagon	"035"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"034"

# Age 4
extract_hexagon	"042"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"035"
extract_hexagon	"043"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"036"
extract_hexagon	"036"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"037"
extract_hexagon	"037"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"038"
extract_hexagon	"040"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"039"
extract_hexagon	"041"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"040"
extract_hexagon	"038"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"041"
extract_hexagon	"039"	"$TOP_LEFT"	"$YELLOW_BORDER"	"042"
extract_hexagon	"044"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"043"
extract_hexagon	"045"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"044"

# Age 5
extract_hexagon	"052"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"045"
extract_hexagon	"053"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"046"
extract_hexagon	"046"	"$TOP_LEFT"	"$RED_BORDER"	"047"
extract_hexagon	"047"	"$BOTTOM_CENTER"	"$RED_BORDER"	"048"
extract_hexagon	"050"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"049"
extract_hexagon	"051"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"050"
extract_hexagon	"048"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"051"
extract_hexagon	"049"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"052"
extract_hexagon	"054"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"053"
extract_hexagon	"055"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"054"

# Age 6
extract_hexagon	"062"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"055"
extract_hexagon	"063"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"056"
extract_hexagon	"056"	"$TOP_LEFT"	"$RED_BORDER"	"057"
extract_hexagon	"057"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"058"
extract_hexagon	"060"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"059"
extract_hexagon	"061"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"060"
extract_hexagon	"058"	"$TOP_LEFT"	"$YELLOW_BORDER"	"061"
extract_hexagon	"059"	"$TOP_LEFT"	"$YELLOW_BORDER"	"062"
extract_hexagon	"064"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"063"
extract_hexagon	"065"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"064"

# Age 7
extract_hexagon	"072"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"065"
extract_hexagon	"073"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"066"
extract_hexagon	"066"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"067"
extract_hexagon	"067"	"$TOP_LEFT"	"$RED_BORDER"	"068"
extract_hexagon	"070"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"069"
extract_hexagon	"071"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"070"
extract_hexagon	"068"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"071"
extract_hexagon	"069"	"$TOP_LEFT"	"$YELLOW_BORDER"	"072"
extract_hexagon	"074"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"073"
extract_hexagon	"075"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"074"

# Age 8
extract_hexagon	"082"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"075"
extract_hexagon	"083"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"076"
extract_hexagon	"076"	"$TOP_LEFT"	"$RED_BORDER"	"077"
extract_hexagon	"077"	"$BOTTOM_LEFT"	"$RED_BORDER"	"078"
extract_hexagon	"080"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"079"
extract_hexagon	"081"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"080"
extract_hexagon	"078"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"081"
extract_hexagon	"079"	"$TOP_LEFT"	"$YELLOW_BORDER"	"082"
extract_hexagon	"084"	"$TOP_LEFT"	"$PURPLE_BORDER"	"083"
extract_hexagon	"085"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"084"

# Age 9
extract_hexagon	"092"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"085"
extract_hexagon	"093"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"086"
extract_hexagon	"086"	"$TOP_LEFT"	"$RED_BORDER"	"087"
extract_hexagon	"087"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"088"
extract_hexagon	"090"	"$TOP_LEFT"	"$GREEN_BORDER"	"089"
extract_hexagon	"091"	"$TOP_LEFT"	"$GREEN_BORDER"	"090"
extract_hexagon	"088"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"091"
extract_hexagon	"089"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"092"
extract_hexagon	"094"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"093"
extract_hexagon	"095"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"094"

# Age 10
extract_hexagon	"102"	"$TOP_LEFT"	"$BLUE_BORDER"	"095"
extract_hexagon	"103"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"096"
extract_hexagon	"096"	"$BOTTOM_LEFT"	"$RED_BORDER"	"097"
extract_hexagon	"097"	"$BOTTOM_LEFT"	"$RED_BORDER"	"098"
extract_hexagon	"100"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"099"
extract_hexagon	"101"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"100"
extract_hexagon	"098"	"$TOP_LEFT"	"$YELLOW_BORDER"	"101"
extract_hexagon	"099"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"102"
extract_hexagon	"104"	"$TOP_LEFT"	"$PURPLE_BORDER"	"103"
extract_hexagon	"105"	"$TOP_LEFT"	"$PURPLE_BORDER"	"104"

# Relics
extract_hexagon	"108"	"$TOP_RIGHT"	"$GREEN_BORDER"	"105"
extract_hexagon	"106"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"106"
extract_hexagon	"109"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"107"
extract_hexagon	"110"	"$BOTTOM_CENTER"	"$RED_BORDER"	"108"
extract_hexagon	"107"	"$TOP_LEFT"	"$YELLOW_BORDER"	"109"

### CITIES ###

# File paths/prefixes
READ_PATH="../cards/Print_CitiesCards_front/Print_CitiesCards_front-"
FILE_SUFFIX="_cities.png"

echo "Extracting Cities hexagon icons..."

# Age 1
extract_hexagon "001" "$TOP_RIGHT"       "$BLUE_BORDER"   "000"
extract_hexagon "013" "$TOP_RIGHT"       "$RED_BORDER"    "001"
extract_hexagon "004" "$TOP_RIGHT"       "$GREEN_BORDER"  "002"
extract_hexagon "007" "$TOP_RIGHT"       "$YELLOW_BORDER" "003"
extract_hexagon "010" "$TOP_RIGHT"       "$PURPLE_BORDER" "004"

# Age 2
extract_hexagon "016" "$TOP_RIGHT"       "$BLUE_BORDER"   "005"
extract_hexagon "024" "$TOP_RIGHT"       "$RED_BORDER"    "006"
extract_hexagon "018" "$TOP_RIGHT"       "$GREEN_BORDER"  "007"
extract_hexagon "020" "$TOP_RIGHT"       "$YELLOW_BORDER" "008"
extract_hexagon "022" "$TOP_RIGHT"       "$PURPLE_BORDER" "009"

# Age 3
extract_hexagon "026" "$TOP_RIGHT"       "$BLUE_BORDER"   "010"
extract_hexagon "034" "$TOP_RIGHT"       "$RED_BORDER"    "011"
extract_hexagon "028" "$TOP_RIGHT"       "$GREEN_BORDER"  "012"
extract_hexagon "030" "$TOP_RIGHT"       "$YELLOW_BORDER" "013"
extract_hexagon "032" "$TOP_RIGHT"       "$PURPLE_BORDER" "014"

# Age 4
extract_hexagon "036" "$TOP_RIGHT"       "$BLUE_BORDER"   "015"
extract_hexagon "044" "$TOP_RIGHT"       "$RED_BORDER"    "016"
extract_hexagon "038" "$TOP_RIGHT"       "$GREEN_BORDER"  "017"
extract_hexagon "040" "$TOP_RIGHT"       "$YELLOW_BORDER" "018"
extract_hexagon "042" "$TOP_RIGHT"       "$PURPLE_BORDER" "019"

# Age 5
extract_hexagon "046" "$TOP_RIGHT"       "$BLUE_BORDER"   "020"
extract_hexagon "054" "$TOP_RIGHT"       "$RED_BORDER"    "021"
extract_hexagon "048" "$TOP_RIGHT"       "$GREEN_BORDER"  "022"
extract_hexagon "050" "$TOP_RIGHT"       "$YELLOW_BORDER" "023"
extract_hexagon "052" "$TOP_RIGHT"       "$PURPLE_BORDER" "024"

# Age 6
extract_hexagon "056" "$TOP_RIGHT"       "$BLUE_BORDER"   "025"
extract_hexagon "064" "$TOP_RIGHT"       "$RED_BORDER"    "026"
extract_hexagon "058" "$TOP_RIGHT"       "$GREEN_BORDER"  "027"
extract_hexagon "060" "$TOP_RIGHT"       "$YELLOW_BORDER" "028"
extract_hexagon "062" "$TOP_RIGHT"       "$PURPLE_BORDER" "029"

# Age 7
extract_hexagon "066" "$TOP_RIGHT"       "$BLUE_BORDER"   "030"
extract_hexagon "074" "$TOP_RIGHT"       "$RED_BORDER"    "031"
extract_hexagon "068" "$TOP_RIGHT"       "$GREEN_BORDER"  "032"
extract_hexagon "070" "$TOP_RIGHT"       "$YELLOW_BORDER" "033"
extract_hexagon "072" "$TOP_RIGHT"       "$PURPLE_BORDER" "034"

# Age 8
extract_hexagon "076" "$TOP_RIGHT"       "$BLUE_BORDER"   "035"
extract_hexagon "084" "$TOP_RIGHT"       "$RED_BORDER"    "036"
extract_hexagon "078" "$TOP_RIGHT"       "$GREEN_BORDER"  "037"
extract_hexagon "080" "$TOP_RIGHT"       "$YELLOW_BORDER" "038"
extract_hexagon "082" "$TOP_RIGHT"       "$PURPLE_BORDER" "039"

# Age 9
extract_hexagon "086" "$TOP_RIGHT"       "$BLUE_BORDER"   "040"
extract_hexagon "094" "$TOP_RIGHT"       "$RED_BORDER"    "041"
extract_hexagon "088" "$TOP_RIGHT"       "$GREEN_BORDER"  "042"
extract_hexagon "090" "$TOP_RIGHT"       "$YELLOW_BORDER" "043"
extract_hexagon "092" "$TOP_RIGHT"       "$PURPLE_BORDER" "044"

# Age 10
extract_hexagon "096" "$TOP_RIGHT"       "$BLUE_BORDER"   "045"
extract_hexagon "104" "$TOP_RIGHT"       "$RED_BORDER"    "046"
extract_hexagon "098" "$TOP_RIGHT"       "$GREEN_BORDER"  "047"
extract_hexagon "100" "$TOP_RIGHT"       "$YELLOW_BORDER" "048"
extract_hexagon "102" "$TOP_RIGHT"       "$PURPLE_BORDER" "049"

### ECHOES ###

# File paths/prefixes
READ_PATH="../cards/Print_EchoesCards_front/Print_EchoesCards_front-"
FILE_SUFFIX="_echoes.png"

echo "Extracting Echoes hexagon icons..."

# Age 1
extract_hexagon "013" "$TOP_LEFT"       "$BLUE_BORDER" "000"
extract_hexagon "014" "$BOTTOM_RIGHT"       "$BLUE_BORDER" "001"
extract_hexagon "015" "$BOTTOM_LEFT"       "$BLUE_BORDER" "002"
extract_hexagon "007" "$TOP_LEFT"       "$RED_BORDER"  "003"
extract_hexagon "008" "$BOTTOM_CENTER"  "$RED_BORDER"  "004"
extract_hexagon "009" "$BOTTOM_CENTER"       "$RED_BORDER"  "005"
extract_hexagon "004" "$BOTTOM_RIGHT"       "$GREEN_BORDER" "006"
extract_hexagon "005" "$BOTTOM_CENTER"  "$GREEN_BORDER" "007"
extract_hexagon "006" "$BOTTOM_CENTER"    "$GREEN_BORDER" "008"
extract_hexagon "001" "$TOP_LEFT"  "$YELLOW_BORDER"    "009"
extract_hexagon "002" "$BOTTOM_LEFT"  "$YELLOW_BORDER"    "010"
extract_hexagon "003" "$BOTTOM_CENTER"  "$YELLOW_BORDER"    "011"
extract_hexagon "010" "$BOTTOM_LEFT"       "$PURPLE_BORDER"   "012"
extract_hexagon "011" "$BOTTOM_LEFT"       "$PURPLE_BORDER"   "013"
extract_hexagon "012" "$TOP_LEFT"       "$PURPLE_BORDER"   "014"



# Age 2
extract_hexagon	"024"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"015"
extract_hexagon	"025"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"016"
extract_hexagon	"020"	"$BOTTOM_LEFT"	"$RED_BORDER"	"017"
extract_hexagon	"021"	"$TOP_LEFT"	"$RED_BORDER"	"018"
extract_hexagon	"018"	"$TOP_LEFT"	"$GREEN_BORDER"	"019"
extract_hexagon	"019"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"020"
extract_hexagon	"016"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"021"
extract_hexagon	"017"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"022"
extract_hexagon	"022"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"023"
extract_hexagon	"023"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"024"


# Age 3
extract_hexagon	"034"	"$TOP_LEFT"	"$BLUE_BORDER"	"025"
extract_hexagon	"035"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"026"
extract_hexagon	"030"	"$TOP_LEFT"	"$RED_BORDER"	"027"
extract_hexagon	"031"	"$BOTTOM_CENTER"	"$RED_BORDER"	"028"
extract_hexagon	"028"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"029"
extract_hexagon	"029"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"030"
extract_hexagon	"026"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"031"
extract_hexagon	"027"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"032"
extract_hexagon	"032"	"$TOP_LEFT"	"$PURPLE_BORDER"	"033"
extract_hexagon	"033"	"$TOP_LEFT"	"$PURPLE_BORDER"	"034"



# Age 4
extract_hexagon	"044"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"035"
extract_hexagon	"045"	"$TOP_LEFT"	"$BLUE_BORDER"	"036"
extract_hexagon	"040"	"$BOTTOM_CENTER"	"$RED_BORDER"	"037"
extract_hexagon	"041"	"$BOTTOM_CENTER"	"$RED_BORDER"	"038"
extract_hexagon	"038"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"039"
extract_hexagon	"039"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"040"
extract_hexagon	"036"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"041"
extract_hexagon	"037"	"$TOP_LEFT"	"$YELLOW_BORDER"	"042"
extract_hexagon	"042"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"043"
extract_hexagon	"043"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"044"


# Age 5
extract_hexagon	"054"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"045"
extract_hexagon	"055"	"$TOP_LEFT"	"$BLUE_BORDER"	"046"
extract_hexagon	"050"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"047"
extract_hexagon	"051"	"$BOTTOM_CENTER"	"$RED_BORDER"	"048"
extract_hexagon	"048"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"049"
extract_hexagon	"049"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"050"
extract_hexagon	"046"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"051"
extract_hexagon	"047"	"$TOP_LEFT"	"$YELLOW_BORDER"	"052"
extract_hexagon	"052"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"053"
extract_hexagon	"053"	"$BOTTOM_RIGHT"	"$PURPLE_BORDER"	"054"



# Age 6
extract_hexagon	"064"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"055"
extract_hexagon	"065"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"056"
extract_hexagon	"060"	"$BOTTOM_CENTER"	"$RED_BORDER"	"057"
extract_hexagon	"061"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"058"
extract_hexagon	"058"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"059"
extract_hexagon	"059"	"$TOP_LEFT"	"$GREEN_BORDER"	"060"
extract_hexagon	"056"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"061"
extract_hexagon	"057"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"062"
extract_hexagon	"062"	"$TOP_LEFT"	"$PURPLE_BORDER"	"063"
extract_hexagon	"063"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"064"


# Age 7
extract_hexagon	"074"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"065"
extract_hexagon	"075"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"066"
extract_hexagon	"070"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"067"
extract_hexagon	"071"	"$TOP_LEFT"	"$RED_BORDER"	"068"
extract_hexagon	"068"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"069"
extract_hexagon	"069"	"$TOP_LEFT"	"$GREEN_BORDER"	"070"
extract_hexagon	"066"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"071"
extract_hexagon	"067"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"072"
extract_hexagon	"072"	"$TOP_LEFT"	"$PURPLE_BORDER"	"073"
extract_hexagon	"073"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"074"



# Age 8
extract_hexagon	"084"	"$TOP_LEFT"	"$BLUE_BORDER"	"075"
extract_hexagon	"085"	"$TOP_LEFT"	"$BLUE_BORDER"	"076"
extract_hexagon	"080"	"$BOTTOM_CENTER"	"$RED_BORDER"	"077"
extract_hexagon	"081"	"$BOTTOM_LEFT"	"$RED_BORDER"	"078"
extract_hexagon	"078"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"079"
extract_hexagon	"079"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"080"
extract_hexagon	"076"	"$TOP_LEFT"	"$YELLOW_BORDER"	"081"
extract_hexagon	"077"	"$BOTTOM_RIGHT"	"$YELLOW_BORDER"	"082"
extract_hexagon	"082"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"083"
extract_hexagon	"083"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"084"


# Age 9
extract_hexagon	"094"	"$BOTTOM_LEFT"	"$BLUE_BORDER"	"085"
extract_hexagon	"095"	"$BOTTOM_CENTER"	"$BLUE_BORDER"	"086"
extract_hexagon	"090"	"$BOTTOM_RIGHT"	"$RED_BORDER"	"087"
extract_hexagon	"091"	"$TOP_LEFT"	"$RED_BORDER"	"088"
extract_hexagon	"088"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"089"
extract_hexagon	"089"	"$BOTTOM_RIGHT"	"$GREEN_BORDER"	"090"
extract_hexagon	"086"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"091"
extract_hexagon	"087"	"$TOP_LEFT"	"$YELLOW_BORDER"	"092"
extract_hexagon	"092"	"$TOP_LEFT"	"$PURPLE_BORDER"	"093"
extract_hexagon	"093"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"094"



# Age 10
extract_hexagon	"104"	"$TOP_LEFT"	"$BLUE_BORDER"	"095"
extract_hexagon	"105"	"$BOTTOM_RIGHT"	"$BLUE_BORDER"	"096"
extract_hexagon	"100"	"$TOP_LEFT"	"$RED_BORDER"	"097"
extract_hexagon	"101"	"$TOP_LEFT"	"$RED_BORDER"	"098"
extract_hexagon	"098"	"$BOTTOM_LEFT"	"$GREEN_BORDER"	"099"
extract_hexagon	"099"	"$BOTTOM_CENTER"	"$GREEN_BORDER"	"100"
extract_hexagon	"096"	"$BOTTOM_LEFT"	"$YELLOW_BORDER"	"101"
extract_hexagon	"097"	"$BOTTOM_CENTER"	"$YELLOW_BORDER"	"102"
extract_hexagon	"102"	"$BOTTOM_CENTER"	"$PURPLE_BORDER"	"103"
extract_hexagon	"103"	"$BOTTOM_LEFT"	"$PURPLE_BORDER"	"104"


### SPRITESHEET ###

echo "Building spritesheet..."

# Create first row of Base hexagon icons separately since it has 15 hexagon icons in it.
magick montage \
temp/00{0..9}_base.png \
temp/0{10..14}_base.png \
-trim -tile 15x1 -geometry 60x60+5+5 -background 'none' temp/base_hexagons_15x.png

# Build remaining 9 rows of Base hexagon icons.
magick montage \
temp/0{15..99}_base.png \
temp/{100..104}_base.png \
-trim -tile 10x9 -geometry 60x60+5+5 -background 'none' temp/base_hexagons_10x.png

# Create first row of Artifacts hexagon icons separately since it has 15 hexagon icons in it.
magick montage \
temp/00{0..9}_artifacts.png \
temp/0{10..14}_artifacts.png \
-trim -tile 15x1 -geometry 60x60+5+5 -background 'none' temp/artifacts_hexagons_15x.png

# Create second row of Artifacts hexagon icons with age 2, then the 5 relics.
magick montage \
temp/0{15..24}_artifacts.png \
temp/{105..109}_artifacts.png \
-trim -tile 15x1 -geometry 60x60+5+5 -background 'none' temp/artifacts_relics_hexagons_15x.png

# Build remaining 8 rows of Artifacts hexagon icons.
magick montage \
temp/0{25..99}_artifacts.png \
temp/{100..104}_artifacts.png \
-trim -tile 10x8 -geometry 60x60+5+5 -background 'none' temp/artifacts_hexagons_10x.png

# Build 9 rows of Cities hexagon icons, to go right of the 10x Base icons.
magick montage \
temp/00{0..9}_cities.png \
temp/0{10..44}_cities.png \
-trim -tile 5x9 -geometry 60x60+5+5 -background 'none' temp/cities_hexagons_10x_1.png

# Build last row of Cities hexagon icons, to go right of the 10x Artifacts icons.
magick montage \
temp/0{45..49}_cities.png \
-trim -tile 5x1 -geometry 60x60+5+5 -background 'none' temp/cities_hexagons_10x_2.png

# Put Cities hexagons to the right of 10x Base hexagons (first 9 rows).
magick montage \
temp/base_hexagons_10x.png \
temp/cities_hexagons_10x_1.png \
-tile 2x1 -geometry +0+0 -background 'none'  -gravity North temp/base_cities_hexagons_10x.png

# Put Cities hexagons to the right of 10x Artifacts hexagons (last row).
magick montage \
temp/artifacts_hexagons_10x.png \
temp/cities_hexagons_10x_2.png \
-tile 2x1 -geometry +0+0 -background 'none'  -gravity North temp/artifacts_cities_hexagons_10x.png


# Combine all images into a single spritesheet.
magick montage \
temp/base_hexagons_15x.png \
temp/base_cities_hexagons_10x.png \
temp/artifacts_hexagons_15x.png \
temp/artifacts_relics_hexagons_15x.png  \
temp/artifacts_cities_hexagons_10x.png \
-tile 1x6 -geometry +0+0 -background 'none' ../../img/hexagon_icons.png

### ECHOES SPRITESHEET ###

echo "Building Echoes spritesheet..."

# Create Echoes hexes in 11x rows, with 11th-15th icons in the 11th column
magick montage \
temp/00{0..9}_echoes.png \
temp/010_echoes.png \
temp/0{15..24}_echoes.png \
temp/011_echoes.png \
temp/0{25..34}_echoes.png \
temp/012_echoes.png \
temp/0{35..44}_echoes.png \
temp/013_echoes.png \
temp/0{45..54}_echoes.png \
temp/014_echoes.png \
-trim -tile 11x5 -geometry 60x60+5+5 -background 'none' temp/echoes_hexagons_11x.png

# Do remaining 5 rows just as 10x rows

magick montage \
temp/0{55..99}_echoes.png \
temp/{100..104}_echoes.png \
-trim -tile 10x5 -geometry 60x60+5+5 -background 'none' temp/echoes_hexagons_10x.png

# Combine all images into a single spritesheet.
magick montage \
temp/echoes_hexagons_11x.png \
temp/echoes_hexagons_10x.png \
-tile 1x2 -geometry +0+0 -background 'none' ../../img/hexagon_icons_echoes.png

echo "Cleaning up..."

# Cleanup
rm -r temp