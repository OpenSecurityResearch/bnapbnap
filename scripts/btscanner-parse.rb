#!/usr/bin/env ruby

if ARGV.length == 0
	puts "Usage: btscanner-parse [btscanner info files]"
	exit 0
end

ARGV.each { |filename|
	f = File.new(filename)
	begin
		while (line = f.readline)
			if line =~ /Address:/
				addrl = line.split
				addr = addrl[-1]
			end
			if line =~ /Class:/
				f.pos+=1
				devicel = f.readline.split(/  /)
				device = devicel[-1]
			end
		end
	rescue EOFError
		bdaddr = addr.split(/:/)
		1.upto(2) do |octet|
			print "INSERT INTO bnap (bdaddr0, bdaddr1, " +
					"bdaddr2, bdaddr3, submitterhost, " +
					"function, timeentered) VALUES (" +
					"\"" + bdaddr[0] + "\", " +
					"\"" + bdaddr[1] + "\", " +
					"\"" + bdaddr[2] + "\", " +
					"\"" + bdaddr[3] + "\", " +
					"\"127.0.0." + octet.to_s + "\", " +
					"\"" + device.chomp + "\", " +
					"DATETIME('NOW'));\n"
		end
		f.close
	end
}

