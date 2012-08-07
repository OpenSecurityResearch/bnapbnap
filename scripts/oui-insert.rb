#!/usr/bin/env ruby

if ARGV.length == 0
	puts "Usage: oui-parse oui.txt"
	exit 0
end

f = File.new(ARGV[0])
begin
	puts "DELETE from oui;"
	while (line = f.readline)
		if line =~ /(hex)/
			# Ignore dups in oui.txt
			next if line =~ /THOMAS CONRAD CORP\./
			next if line =~ /ROYAL MELBOURNE INST OF TECH/
			next if line =~ /NETWORK RESEARCH CORPORATION/

			# Remove " for SQL INSERT
			line = line.gsub(/"/, "") if line =~ /"/

			# Remove , for CSV consistency
			line = line.gsub(/,/, "") if line =~ /,/

			vendorl = line.split(/	/)
			vendor = vendorl[-1].chomp
			ouil = line.split(/[- ]/)
			oui = ouil[0] + ":" + ouil[1] + ":" + ouil[2]
			print "INSERT INTO oui VALUES (\"" + oui + "\", \"" +
					vendor + "\");\n"
		end
	end
rescue EOFError
	puts ""
end
