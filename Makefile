#
# Simple makefile for installing scripts / web stuff
# F. Montorsi
# 26 Dec. 2015
#

current_dir = $(shell pwd)

all:
	@echo "Open the Makefile to get a list of possible targets you can invoke"

update-install-folder:
	# by default all bash glue scripts assume /opt/light-media-center is used as installation folder...
	# if that's not the case some SED is required:
	sed -i "s+/opt/light-media-center+$(current_dir)+g" bin/*.sh bin/inc/*.inc.sh etc/init.d/*.sh etc/system.d/*.service

download-aux:
	# install auxiliary software under "web":
	if [ ! -d "web/webui-aria2" ]; then 	mkdir -p web/webui-aria2 && cd web && git clone https://github.com/ziahamza/webui-aria2.git ; fi
	if [ ! -d "web/yaaw" ]; then			mkdir -p web/yaaw && cd web && git clone https://github.com/binux/yaaw.git ; fi
	if [ ! -d "web/_h5ai" ]; then			mkdir -p web/_h5ai && cd web && wget https://release.larsjung.de/h5ai/h5ai-0.28.1.zip && unzip h5ai-0.28.1.zip ; fi

install-links:
	if [ -d "/var/www/html" ]; then                  mv /var/www/html /var/www/htmlOLD ; fi
	ln -sf $(current_dir)/web /var/www/html
	cd bin && for script in *.sh; do                 ln -sf $(current_dir)/bin/$$script                 /usr/local/bin/$$script ;  done
	cd bin/aria2utils && for script in *; do         ln -sf $(current_dir)/bin/aria2utils/$$script      /usr/local/bin/$$script ;  done
	cd bin/minidlna_utils && for script in *; do     ln -sf $(current_dir)/bin/minidlna_utils/$$script  /usr/local/bin/$$script ;  done

install-cron:
	ln -sf $(current_dir)/etc/cron.d/light-media-center /etc/cron.d/light-media-center

install-initd:
	# use this target only if your distribution is still using SysV init scripts, otherwise use install-systemd
	cp -pf etc/init.d/btmain /etc/init.d/btmain
	cp -pf etc/init.d/mldonkey-server /etc/init.d/mldonkey-server
	cp -pf etc/init.d/noip2 /etc/init.d/noip2
	cp -pf etc/init.d/aria2 /etc/init.d/aria2
	update-rc.d btmain defaults
	update-rc.d mldonkey-server defaults
	update-rc.d noip2 defaults
	update-rc.d aria2 defaults

install-initd-email-on-boot:
	cp -pf etc/init.d/btemailnotify /etc/init.d/btemailnotify
	update-rc.d btemailnotify defaults
    
install-systemd:
	# IMPORTANT: apparently symlinks do not work well with SystemD, so we must copy files;
	#            if we don't do that, we will be able to start/stop services but not to enable them on boot!
	cp -f $(current_dir)/etc/system.d/btmain.service /lib/systemd/system/btmain.service
	cp -f $(current_dir)/etc/system.d/minidlnad.service /lib/systemd/system/minidlnad.service
	cp -f $(current_dir)/etc/system.d/btemailnotify.service /lib/systemd/system/btemailnotify.service
	# TODO remaining ones!
	systemctl daemon-reload
	systemctl enable btmain   # start btmain on boot

install-systemd-email-on-boot:
	systemctl enable btemailnotify
    
install-watchdog:
	# use this target only if you have /root/reset_watchdog utility and soft-watchdog feature enabled
	cp -pf etc/init.d/btwatchdog /etc/init.d/btwatchdog
	update-rc.d btwatchdog defaults

install-logrotate:
	ln -sf $(current_dir)/etc/logrotate.d/aria2 /etc/logrotate.d/
	ln -sf $(current_dir)/etc/logrotate.d/btmain /etc/logrotate.d/
	ln -sf $(current_dir)/etc/logrotate.d/minidlna /etc/logrotate.d/


