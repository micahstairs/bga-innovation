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

import os
import mmap

# File the SCSS code will be written in
dest_file = "..\innovationlordmcfuzz.scss"

def write_font_sizes(f):
	for s in range(1, 31):
		remSize = (s * 0.0625)
		f.write(""".font_size_{s} <
\tfont-size: {remSize}rem;
>\n\n""".format(s=s,remSize=remSize).replace('<', '{').replace('>', '}'))

def write_hexagon_icons(f, size, x, y, offset):
	x_reset = x
	f.write(""".{size} < \n""".format(size=size).replace('<', '{'))
	f.write("\t&.hexagon_card_icon {\n")
	# Base cards 
	for id in range(0,105):
		f.write("""\t\t&.hexagon_icon_{id} <
\t\t\tbackground-position: -{x}px -{y}px;
\t\t>\n""".format(id=id, x=x, y=y).replace('<', '{').replace('>', '}'))
		
		if id >= 14 and (id - 14) % 10 == 0: # New line on sprite
			x = x_reset
			y += offset
		else: # Same line, next card 
			x += offset
	

	# Artifact cards
	for id in range(110,215):
		f.write("""\t\t&.hexagon_icon_{id} <
\t\t\tbackground-position: -{x}px -{y}px;
\t\t>\n""".format(size=size, id=id, x=x, y=y).replace('<', '{').replace('>', '}'))
		
		if id >= 124 and (id - 124) % 10 == 0: # New line on sprite
			x = x_reset
			y += offset
		else: # Same line, next card 
			x += offset
	
	# Relic cards
	# Back up y x and y offset to the end of age 2 of Artifact cards
	x_temp = x
	y_temp = y
	y -= offset * 9
	x += offset * 10
	for id in range (215,220):
		f.write("""\t\t&.hexagon_icon_{id} <
\t\t\tbackground-position: -{x}px -{y}px;
\t\t>\n""".format(size=size, id=id, x=x, y=y).replace('<', '{').replace('>', '}'))
		x += offset
	x = x_temp
	y = y_temp

	# New hex icon expansion code goes here
	f.write("\t}\n} \n\n")

if __name__ == "__main__":
	content= ''
	comment = '/* scss_generator code starts here */'
	with open(os.path.join(os.path.dirname(__file__), dest_file), "r") as f:
		content = f.read()
		content = content.split(comment)[0]
	with open(os.path.join(os.path.dirname(__file__), dest_file), "w") as f:
		f.write(content)
	with open(os.path.join(os.path.dirname(__file__), dest_file), "a") as f:
		f.write(comment + '\n/* anything below this will get blown away on rebuild */\n\n')
		write_font_sizes(f)
		write_hexagon_icons(f, "M", 3, 3, 49)
		write_hexagon_icons(f, "L", 8, 8, 101)