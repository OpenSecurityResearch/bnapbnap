#!/usr/bin/env python
#
# Basic parser for bluetooth UAP/NAP info 
# from http://www.hackfromacave.com/projects/bpp.html
#
# brad.antoniewicz@foundstone.com
#

import sys;

if len(sys.argv) != 2:
	print "Usage:"
	print sys.argv[0] + " bluetooth_profile_list.txt"
	exit(-1);
hosts = [ "127.0.0.1", "127.0.0.2" ];

f = open(sys.argv[1], 'r');

for line in f:
	fields = line.split(","); 
	mac = fields[0].split(":");
	for host in hosts:
		sqlline =  "INSERT INTO bnap (bdaddr0, bdaddr1, bdaddr2, bdaddr3, submitterhost, function, timeentered) ";
		sqlline += "VALUES (";
		sqlline += "\"" + mac[0] + "\", ";
		sqlline += "\"" + mac[1] + "\", ";
		sqlline += "\"" + mac[2] + "\", ";
		sqlline += "\"" + mac[3] + "\", ";
		sqlline += "\"" + host + "\", ";
		sqlline += "\"" + fields[1] + "\", "; 
		sqlline += "DATETIME(\'NOW\'));";
		print sqlline;

