#
# Simple makefile for installing scripts / web stuff
# F. Montorsi
# 26 Dec. 2015
#

current_dir = $(shell pwd)

all:
	@echo "Open the Makefile to get a list of possible targets you can invoke"

update-install-folder:
	# by default all bash glue scripts assume /opt/light-media-center is used as installation folder... if that's not the case some SED is required:
	sed -i "s+/opt/light-media-center+$current_dir+g" bin/btmain.sh

download-aux:
	# install auxiliary software under "web":
	if [[ ! -d "web/webui-aria2" ]]; then	mkdir -p web/webui-aria2 && cd web && git clone https://github.com/ziahamza/webui-aria2.git ; fi
	if [[ ! -d "web/yaaw" ]]; then			mkdir -p web/yaaw && cd web && git clone https://github.com/binux/yaaw.git ; fi
	if [[ ! -d "web/_h5ai" ]]; then			mkdir -p web/_h5ai && cd web && wget https://release.larsjung.de/h5ai/h5ai-0.28.1.zip && unzip h5ai-0.28.1.zip ; fi

install-links:
	ln -s $(current_dir)/web /var/www/html
	echo "export PATH=$(current_dir)/bin:$$PATH" >>/etc/environment

install-cron:
	echo "# Light Media Center cron script" >/etc/cron.d/light-media-center
	echo "# check external disk every day at 8am" >>/etc/cron.d/light-media-center
	echo "0 8 * * * $(current_dir)/bin/btextdiskcheck.sh >/dev/null 2>&1" >>/etc/cron.d/light-media-center
	echo "# automatic rescan of media contents every day at 5pm" >>/etc/cron.d/light-media-center
	echo "0 17 * * * $(current_dir)/bin/btminidlnareload.sh >/dev/null 2>&1" >>/etc/cron.d/light-media-center

install-initd:
	cp etc/init.d/btmain /etc/init.d/btmain
	cp etc/init.d/btwatchdog /etc/init.d/btwatchdog
	cp etc/init.d/mldonkey-server /etc/init.d/mldonkey-server
	cp etc/init.d/noip2 /etc/init.d/noip2
	cp etc/init.d/aria2 /etc/init.d/aria2
	update-rc.d btmain defaults
	update-rc.d btwatchdog defaults
	update-rc.d mldonkey-server defaults
	update-rc.d noip2 defaults
	update-rc.d aria2 defaults
	
install-logrotate:
	cp etc/logrotate.d/aria2 /etc/logrotate.d/
	cp etc/logrotate.d/btmain /etc/logrotate.d/
	cp etc/logrotate.d/minidlna /etc/logrotate.d/

install-email-on-boot:
	echo "# send an email to inform about the boot" >>/etc/rc.local
	echo "/usr/local/bin/btemailnotify.sh &" >>/etc/rc.local
