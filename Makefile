#
# Simple makefile for installing scripts / web stuff
# F. Montorsi
# 26 Dec. 2015
#

current_dir = $(shell pwd)

all:
	

install-links:
	ln -s $(current_dir)/web /var/www/html
