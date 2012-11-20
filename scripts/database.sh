#!/bin/bash
# Created by Josh Wright
# updated by brad antoniewicz

SQLITE_BIN=sqlite3

echo "[+] Creating bnapbnap.db"
echo -e "[+] \tCreating bnap table"
$SQLITE_BIN bnapbnap.db "
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
		PRIMARY KEY (bdaddr0, bdaddr1, bdaddr2, submitterhost)
	)"
echo -e "[+] \tCreating oui table"
$SQLITE_BIN bnapbnap.db "
	CREATE TABLE oui (
		oui		TEXT NOT NULL,
		vendor		TEXT NOT NULL,
		PRIMARY KEY (oui)
	)"

echo "[+] Sanitizing oui.txt"
iconv -f windows-1252 -t utf-8 oui.txt > oui.iconv

echo "[+] Importing oui.iconv into bnapbnap.db"
./oui-insert.rb oui.iconv | $SQLITE_BIN bnapbnap.db

echo "[+] Importing jlw-entries.sql into bnapbnap.db"
cat jlw-entries.sql | $SQLITE_BIN bnapbnap.db 
#sort -u jlw-entries.sql | grep -v "127.0.0.2" | $SQLITE_BIN bnapbnap.db 

echo "[+] Cleaning up"
rm oui.iconv

