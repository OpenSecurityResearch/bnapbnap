#!/bin/bash
# Created by Josh Wright
# updated by brad antoniewicz


echo "[+] Creating bnapbnap.db"
echo -e "[+] \tCreating bnap table"
sqlite bnapbnap.db "
	CREATE TABLE bnap (
		bdaddr0		TEXT NOT NULL,
		bdaddr1		TEXT NOT NULL,
		bdaddr2		TEXT NOT NULL,
		bdaddr3		TEXT DEFAULT \"\",
		submitterhost	TEXT NOT NULL,
		partnum		TEXT DEFAULT \"\",
		manuf		TEXT DEFAULT \"\",
		function	TEXT DEFAULT \"\",
		timeentered	DATETIME NOT NULL,
		PRIMARY KEY (bdaddr0, bdaddr1, bdaddr2, bdaddr3, submitterhost)
	)"
echo -e "[+] \tCreating oui table"
sqlite bnapbnap.db "
	CREATE TABLE oui (
		oui		TEXT NOT NULL,
		vendor		TEXT NOT NULL,
		PRIMARY KEY (oui)
	)"

echo "[+] Sanitizing oui.txt"
iconv -f windows-1252 -t utf-8 oui.txt > oui.iconv

echo "[+] Importing oui.iconv into bnapbnap.db"
./oui-insert.rb oui.iconv | sqlite bnapbnap.db

echo "[+] Importing jlw-entries.sql into bnapbnap.db"
cat jlw-entries.sql | sqlite bnapbnap.db 
#sort -u jlw-entries.sql | sqlite bnapbnap.db 

echo "[+] Cleaning up"
rm oui.iconv

