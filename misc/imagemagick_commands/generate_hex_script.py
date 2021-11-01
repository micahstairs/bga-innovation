# Small script to generate shell script text for hexagon extraction 
# Note that the hex positions still need to be manually changed

# Offsets to match 
offset_list = [6,-2,0,-4,0]

# Make lines for age 2-10
color_list = ['BLUE', 'RED', 'GREEN', 'YELLOW', 'PURPLE']
with open ("hex_extract_stub.txt", "w") as f:
    for i in range(16,106):
        
        #Write age number in every chunk of 10
        if (i-16) %10 == 0:
            f.write("\n")
            f.write(f"# Age {(i-16)//10 + 2}")
            f.write("\n")
            
        # Get offset for read file
        offset_index = (i-16)%10 //2
        read_num = str(i+offset_list[offset_index]).zfill(3)
        write_num = str(i-1).zfill(3)
        color = color_list[(i-16) % 10 // 2]
        
        # Write to file
        f.write("\t".join(["make_hexagon", f'"{read_num}"', '"$TOP_LEFT"',  f'"${color}_BORDER"', f'"{write_num}"']))
        f.write("\n")
