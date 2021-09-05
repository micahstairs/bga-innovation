#!/usr/bin/python3.5
# -*- coding: utf8 -*-
"""
BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
Innovation implementation : © Jean Portemer <jportemer@gmail.com>

This file is design to produce hard css code to be appended into innovation.js, in order to manage sprite management using HTML classes and fonts.
It works with a Python interpreter (version >= 3.5).
The generated file is created or recreated in the same folder this script is executed, its name is :
to_be_appended_into_innovation.css
"""

#----------------------------------------------------------------------------------------------------

# File the CSS code will be written in
dest_file = "to_be_appended_into_innovation.css"

# Characteristics of the sprite for:
#	-cards in high resolution
#	-fonts
# The values in it MUST be consistent with those which can be found in innovation.js and innovation.css
data = [
	{
		# Data for cards size S (special achievements)
		"base_class" : ".S.card",
		"x_min" : 490,
		"y_min" : 35,
		"delta_x" : 49, # width + 2 * offset
		"delta_y" : 35, # height + 2 * offset
		"offset": 2,
	},
	
	{
		# Data for rectos size S
		"base_class" : ".S.recto",
		"x_min" : 630,
		"y_min" : 105,
		"delta_x" : 35, # width + 2 * offset
		"delta_y" : 49, # height + 2 * offset
		"offset": 2,
	},
	
	{
		# Data for cards size M
		"base_class" : ".M.card",
		"x_min" : 0,
		"y_min" : 0,
		"delta_x" : 196, # width + 2 * offset
		"delta_y" : 140, # height + 2 * offset
		"offset": 8,
	},
	
	{
		# Data for cards size L
		"base_class" : ".L.card",
		"x_min" : 2,
		"y_min" : 2,
		"delta_x" : 490, # width + 2 * offset
		"delta_y" : 350, # height + 2 * offset
		"offset": 16,
	},
	
	{
		# Data for cards size L (special achievements)
		"base_class" : ".L.card",
		"x_min" : 4902,
		"y_min" : 352,
		"delta_x" : 490, # width + 2 * offset
		"delta_y" : 350, # height + 2 * offset
		"offset": 16,
	},
	
	{
		# Data for cards size L (rectos)
		"base_class" : ".L.recto",
		"x_min" : 6302,
		"y_min" : 1052,
		"delta_x" : 350, # width + 2 * offset
		"delta_y" : 490, # height + 2 * offset
		"offset": 16,
	},
		
	{
		# Data for font scales
		"max_size": 30
	}
]
#----------------------------------------------------------------------------------------------------


#----------------------------------------------------------------------------------------------------
def write_card_sprites(f, specific_data):
	x = specific_data["x_min"]
	y = specific_data["y_min"]
	offset = specific_data["offset"]
	
	# Loop on cards
	for id in range(0,105):
		f.write(""".item_{id}{base_class} <
\tbackground-position: -{x}px -{y}px;
>\n\n""".format(base_class=specific_data['base_class'], id=id, x=x+offset, y=y+offset).replace('<', '{').replace('>', '}'))
		
		if id >= 14 and (id - 14) % 10 == 0: # New line on sprite
			x = specific_data["x_min"]
			y += specific_data["delta_y"]
		else: # Same line, next card 
			x += specific_data["delta_x"]
#----------------------------------------------------------------------------------------------------


#----------------------------------------------------------------------------------------------------
def write_special_achievements_sprites(f, specific_data):
	# Initialisation of position parameters
	x = specific_data["x_min"]
	y = specific_data["y_min"]
	offset = specific_data["offset"]
	
	# Loop on cards
	for id in range(105, 110):
		specific_class = ".item_{id}".format(id=id)
		f.write("""{specific_class}{base_class} <
	\tbackground-position: -{x}px -{y}px;
	>\n\n""".format(specific_class=specific_class, base_class=specific_data['base_class'], x=x+offset, y=y+offset).replace('<', '{').replace('>', '}'))
		
		x += specific_data["delta_x"]
#----------------------------------------------------------------------------------------------------

#----------------------------------------------------------------------------------------------------
def write_recto_sprites(f, specific_data):
	# Initialisation of position parameters
	x = specific_data["x_min"]
	y = specific_data["y_min"]
	offset = specific_data["offset"]
	
	# Loop on cards
	for age in range(1, 16):
		if age <= 10:
			specific_class = ".age_{age}".format(age=age)
		else:
			specific_class = ".item_{id}".format(id=age+94)
		f.write("""{specific_class}{base_class} <
	\tbackground-position: -{x}px -{y}px;
	>\n\n""".format(specific_class=specific_class, base_class=specific_data['base_class'], x=x+offset, y=y+offset).replace('<', '{').replace('>', '}'))
		
		if age % 5 == 0: # New column on sprite
			y = specific_data["y_min"]
			x += specific_data["delta_x"]
		else: # Same line, next card 
			y += specific_data["delta_y"]
#----------------------------------------------------------------------------------------------------

#----------------------------------------------------------------------------------------------------
def write_font_sizes(data):
	for s in range(1, data['max_size']+1):
		f.write(""".font_size_{s} <
\tfont-size: {s}px;
>\n\n""".format(s=s).replace('<', '{').replace('>', '}'))
#----------------------------------------------------------------------------------------------------

if __name__ == "__main__":
	with open(dest_file, "w") as f:
		f.write("/* Auto-generated by css_generator_for_sprites.py (Python script) */\n\n")

		write_special_achievements_sprites(f, data[0])
		write_recto_sprites(f, data[1])
		write_card_sprites(f, data[2])
		
		write_card_sprites(f, data[3])
		write_special_achievements_sprites(f, data[4])
		write_recto_sprites(f, data[5])
			
		write_font_sizes(data[-1])
