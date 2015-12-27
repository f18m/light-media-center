#
# Simple makefile for installing scripts / web stuff
# F. Montorsi
# 26 Dec. 2015
#

current_dir = $(shell pwd)

all:
	

download-aux:
	# install auxiliary software:
	mkdir web/webui-aria2 && cd web/webui-aria2 && git clone https://github.com/ziahamza/webui-aria2.git
	mkdir web/yaaw && cd web/yaaw && git clone https://github.com/binux/yaaw.git
	mkdir web/_h5ai && cd web/_h5ai && wget https://release.larsjung.de/h5ai/h5ai-0.28.1.zip && unzip h5ai-0.28.1.zip

install-links:
	ln -s $(current_dir)/web /var/www/html
	echo "export PATH=$(current_dir)/bin:$$PATH" >>/etc/environment

install-cron:
	echo "# add automatic rescan of media contents every day at 7:00am" >>/etc/cron.d/light-media-center
	echo "0 8 * * * $(current_dir)/bin/btextdiskcheck.sh >/dev/null 2>&1" >>/etc/cron.d/light-media-center
	echo "0 17 * * * $(current_dir)/bin/btminidlnareload.sh >/dev/null 2>&1" >>/etc/cron.d/light-media-center
