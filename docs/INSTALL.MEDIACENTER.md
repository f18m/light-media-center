# Configuration of Light Media Center #

Note that all install procedures that are easy and safe to automate have been included in the Light Media Center makefile as install-SOMETHING targets; however tasks that are non-trivial to automate or strongly dependent upon the specific Linux distribution version or 3rd party software versions are instead listed here as manual steps.

Note that generally speaking all the following configurations and commands must be run as root.


## Some Info about Light Media Center Configuration ##

Light Media Center control scripts take care of running/restarting (as a sort of watchdog):
 - miniDLNA
 - Aria2
 - MLdonkey
 
The idea is that these softwares need to be run only under some conditions (e.g., presence of the external hard disk for storage)
rather than being started unconditionally on boot. Moreover having an "orchestrator" process (in this case Light Media Center control scripts) 
allows to e.g., rescan media folders only when those have been fully downloaded and only when necessary.

Since the single-board computers targeted by Light Media Center (OLinuxino,BeagleBone, Raspberry PI, etc) usually employ a FLASH memory or an SD card
as storage for the root filesystem, all media files and download folders (for e.g., Aria2 and MLdonkey peer to peer file sharing clients) obviously need
to be hosted on a different storage media (usually a magnetic hard drive) that is later referred as the "external hard disk".




## 0) Setup of External Hard Disk ##

First of all, format your external hard disk as ext3 or ext4. This can be done using "gparted" from a Linux computer or
using commercial software suites (e.g., Paragon Hard Disk Manager Suite) on a Windows computer.
The ext3 or ext4 partition of the external hard disk is supposed to be named "LMC" in the following.

Then, on the SBC check that the disk can be correctly mounted:

```
mkdir -p /media/extdisc
ls -l /dev/disk/by-label
 # verify that your hard disk is recognized
mount /dev/disk/by-label/LMC /media/extdisc
echo test >/media/extdisc/test-file
sync
cat /media/extdisc/test-file
 # verify the file was correctly written, eventually check "dmesg" to inspect kernel/driver activities
rm /media/extdisc/test-file
```

Finally setup some folders that will be used by softwares installed later.
- The folder where Aria2 stores some meta-data information:
```
mkdir -p /media/extdisc/.aria2
```

- The folder where Aria2 stores incoming torrent files:
```
mkdir -p /media/extdisc/.in-download/torrents
```

- The folder where Aria2 stores completely-downloaded files:
```
mkdir -p /media/extdisc/to_reorder
```

- A symlink to the incoming files and to the downloaded ones:

```
mkdir -p /var/www/html/extdiscTORRENTS /var/www/html/extdiscMAIN
ln -s /media/extdisc/.in-download/torrents  /var/www/html/extdiscTORRENTS
ln -s /media/extdisc/to_reorder             /var/www/html/extdiscMAIN
```

Finally, ensure correct permissions are set for the user "debian":

```
chown -R debian:debian /media/extdisc/.aria2 /media/extdisc/.in-download/torrents /media/extdisc/to_reorder   /var/www/html/extdiscTORRENTS /var/www/html/extdiscMAIN
chmod -R ug+rw /media/extdisc/.aria2 /media/extdisc/.in-download/torrents /media/extdisc/to_reorder   /var/www/html/extdiscTORRENTS /var/www/html/extdiscMAIN
```


## 1) Download of Light Media Center ##

```
cd /opt
git clone https://github.com/f18m/light-media-center.git
cd light-media-center
make download-aux
make install-links
make install-cron
make install-logrotate
```

An optional feature you may like is an automatic email upon each boot (assuming that you reboot your media center rarely):

```
make install-initd-email-on-boot     # or 'install-systemd-email-on-boot' if you are using SystemD instead of initd
```

Depending on which boot system is used by your distribution (init.d or system.d) you need to run:

```
make install-initd
```

or

```
make install-systemd
```

Now configure main script options:

```
nano /opt/light-media-center/bin/inc/btmain.inc.sh
```

In particular, configure the label of your external disk partition, which services do you want to start upon external disk attach, etc.



## 2) Configure MINIDLNA stuff ##

```
cd /opt
wget http://sourceforge.net/projects/minidlna/files/latest/download
tar -xvzf download
rm download

cd minidlna-1.1.5/
apt-get install -y libavformat-dev libavutil-dev libavcodec-dev libflac-dev libvorbis-dev libid3tag0-dev libexif-dev libjpeg-dev libsqlite3-dev libogg-dev gettext
./configure
make
make install-strip
cp minidlna.conf /etc
cp linux/minidlna.init.d.script /etc/init.d/minidlna
chmod a+x /etc/init.d/minidlna

echo >/var/log/minidlna.log
mkdir -p /var/cache/minidlna
chown -R debian:debian /var/cache/minidlna /var/log/minidlna.log
```

Then allow minidlna to monitor many files:

```
nano /etc/sysctl.conf 

------------------ cut here ----------------------
# for miniDLNA:
fs.inotify.max_user_watches=250000
------------------ cut here ----------------------
```


Now make minidlna scan the external disk:

```
nano /etc/minidlna.conf
```

------------------ cut here ----------------------
```
# specify the user account name or uid to run as
user=debian

# set this to the directory you want scanned.
# * if you want multiple directories, you can have multiple media_dir= lines
# * if you want to restrict a media_dir to specific content types, you
#   can prepend the types, followed by a comma, to the directory:
#   + "A" for audio  (eg. media_dir=A,/home/jmaggard/Music)
#   + "V" for video  (eg. media_dir=V,/home/jmaggard/Videos)
#   + "P" for images (eg. media_dir=P,/home/jmaggard/Pictures)
#   + "PV" for pictures and video (eg. media_dir=AV,/home/jmaggard/digital_camera)
media_dir=/media/minidlna

# set this if you want to customize the name that shows up on your clients
friendly_name=LightMC

# Path to the directory that should hold the database and album art cache.
db_dir=/var/cache/minidlna

# Path to the directory that should hold the log file.
log_dir=/var/log
```
------------------ cut here ----------------------

Test that it works correctly:

```
mkdir -p /media/minidlna
cd /media/minidlna && ln -s /media/extdisc/YOUR_MOVIES_FOLDER .

minidlnad
pgrep minidlnad   # verify it is up and running
pkill minidlnad   # stop it
```

 
 
 
## 3) Configure DynamicDNS services ##

### 3.1) Configure No-IP client

In case you are using noip.com as DDNS provider, you can easily install on your Light Media Center
the no-ip client for linux.
The following commands were taken from http://www.noip.com/support/knowledgebase/installing-the-linux-dynamic-update-client/

```
cd /opt
wget http://www.no-ip.com/client/linux/noip-duc-linux.tar.gz
tar xzf noip-duc-linux.tar.gz && cd no-ip-2.1.9-1
make && make install
```

Test that it works correctly:

```
/etc/init.d/noip2 start
pgrep noip2
```

Install it permanently:

```
cp debian.noip2.sh /etc/init.d/noip2
update-rc.d noip2 defaults
```


### 3.2) Configure DD Client

In case your DDNS provider is something like changeip.com, then you will need to use their "DD Client" 
on your Light Media Center

```
apt-get install ddclient
```

This will guide you into setup and configuration of the client... in my case ddclient did not work 
with changeip.com (at least at the time of this writing: Nov 2019)... so I used instead the "rinker.sh"
script that can be downloaded from 
https://www.changeip.com/accounts/index.php?rp=/download/category/4/Linux-or-OSX.html


## 4) Configure ARIA2 ##

Note that packaged aria2 in Debian sid is too old (version 1.33 currently) so it's best to recompile it.
First visit https://github.com/tatsuhiro-t/aria2/releases/latest to find out the latest available release,
then:

```
cd /opt
wget https://github.com/aria2/aria2/releases/download/release-1.33.0/aria2-1.33.0.tar.gz
tar -xvf aria2-1.33.0.tar.gz && rm aria2-1.33.0.tar.gz && cd aria2-1.33.0/

apt-get install -y libxml2-dev nettle-dev libssl-dev libgcrypt-dev libgnutls28-dev libxml2-dev libcppunit-dev pkg-config automake autopoint libtool

autoreconf -i
./configure --prefix=/usr
```

verify the output:

```
configure: summary of build options:

    version:        0.1.1 shared 0:0:0
    Host type:      armv7l-unknown-linux-gnueabihf
    Install prefix: /usr
    C compiler:     gcc
    CFlags:         -g -O2
    Library types:  Shared=yes, Static=yes
    CUnit:          no


Build:          armv7l-unknown-linux-gnueabihf
Host:           armv7l-unknown-linux-gnueabihf
Target:         armv7l-unknown-linux-gnueabihf
Install prefix: /usr
CC:             gcc
CXX:            g++
CPP:            gcc -E
CXXFLAGS:       -g -O2 -pipe -std=c++11
CFLAGS:         -g -O2 -pipe
CPPFLAGS:       -I$(top_builddir)/deps/wslay/lib/includes -I$(top_srcdir)/deps/wslay/lib/includes -I/usr/include/p11-kit-1  -I/usr/include/libxml2
LDFLAGS:
LIBS:           -lgmp -lnettle -lgnutls -lsqlite3 -lxml2 -lz
DEFS:           -DHAVE_CONFIG_H
LibUV:
SQLite3:        yes
SSL Support:    yes
AppleTLS:
WinTLS:         no
GnuTLS:         yes
OpenSSL:
CA Bundle:
LibXML2:        yes
LibExpat:
LibCares:       no
Zlib:           yes
Libssh2:        no
Epoll:          yes
Bittorrent:     yes
Metalink:       yes
XML-RPC:        yes
Message Digest: libnettle
WebSocket:      yes
Libaria2:       no
bash_completion dir: ${datarootdir}/doc/${PACKAGE_TARNAME}/bash_completion
Static build:
```

```
make && make install-strip
# go take a coffeee!!! takes >1h
```

As of aria2 1.19.3, aria2 does not come with a default etc file, so a default one is included in Light Media Center sources:

```
cp /opt/light-media-center/etc/aria2.conf /etc
cp /opt/light-media-center/etc/init.d/aria2 /etc/init.d/

echo >/var/log/aria2.log

mkdir -p /media/extdisc/.in-download/aria2
chown -R debian:debian /media/extdisc/.in-download/aria2  /var/log/aria2.log
chmod -R ug+rw /media/extdisc/.in-download/aria2 /var/log/aria2.log
touch /media/extdisc/.in-download/aria2/last-session

/etc/init.d/aria2 start
```

Use aria2q utility to verify it's working:

```
aria2q
```

If aria2 does not start in daemon mode, you can comment out the "daemon" keyword in the /etc/aria2.conf file
and try start it from command line:

```
aria2c --rpc-listen-all --enable-rpc
```

Then modify RPC port and password if needed and set them in the webui-aria2 config file:

```
nano /var/www/html/webui-aria2/configuration.js
```



### 5) Configure MLDONKEY ##

```
apt-get install -y mldonkey-server telnet
```

in /etc/default/mldonkey-server

------------------ cut here ----------------------
```
    MLDONKEY_USER=debian
    MLDONKEY_GROUP=debian
```
------------------ cut here ----------------------

then

```
   su debian
   mlnet
```

Login from another terminal to set the password for accessing as administrator the MLdonkey web interface:

```
   $ telnet 127.0.0.1 4000
   > auth admin ""
   > passwd ubuntu
   > set allowed_ips 255.255.255.255
   > quit
```

Then open everywhere in your LAN the port 4080
   
   

## 6) Configure UPRECORDS ##

Uprecords is a nice utility keeping track of the highest uptimes a computer system ever had; 
its stats are shown via Light Media Center "check system status" web page:

```
apt-get install -y uptimed
```

   
   
## 7) Configure WEB INTERFACE (IN LIGHTTPD) ##

### Raspberry PI note ###

If Kodi is running, via the graphical user interface, disable kodi webserver:
```
 Settings → Services → Webserver → Allow control of XBMC/Kodi via HTTP
```


### Webserver setup ###
 
```
apt-get install -y lighttpd php5-common php5-cgi php5

# the apache user www-data must be in the debian group:
/usr/sbin/usermod -a -G debian www-data

# ensure that /var/www/* are read/write for debian group:
chown -R debian:debian /var/www
chmod -R ug+rw /var/www
```

Now make sure that the www-data user is enabled to elevate to root permissions;
this is very unsecure but it is quick to setup (WORK IN PROGRESS):

```
sudo visudo
------------------ cut here ----------------------
www-data ALL=(ALL) NOPASSWD: ALL
------------------ cut here ----------------------
```

Then enable PHP modules inside Lighttppd server:

```
/usr/sbin/lighty-enable-mod fastcgi
/usr/sbin/lighty-enable-mod fastcgi-php
/usr/sbin/lighty-enable-mod auth

nano /etc/lighttpd/conf-enabled/15-fastcgi-php.conf
```

And set:

```
------------------ cut here ----------------------
                        "PHP_FCGI_CHILDREN" => "2",
------------------ cut here ----------------------
```

to save memory. Then, enable h5ai file indexing:

```
nano /etc/lighttpd/lighttpd.conf
```

and locate the row with index.html and index.php and change it to:

```
------------------ cut here ----------------------
index-file.names += ("index.html", "index.php", "/_h5ai/public/index.php")
------------------ cut here ----------------------
```

Finally attempt webserver start:

```
service lighttpd restart
```

Verify that the server is working by connecting via a web browser to the IP address of your SBC.



## 8) Configure dumptorrent ##

```
cd /opt
wget http://sourceforge.net/projects/dumptorrent/files/dumptorrent/1.2/dumptorrent-1.2.tar.gz/download 
mv download dumptorrent-1.2.tar.gz
tar -xvzf dumptorrent-1.2.tar.gz
cd dumptorrent-1.2
make   # note that as of v1.2 there is no "install" target
cp dumptorrent /usr/local/bin
```



## FINAL CHECKS ##

```
reboot
```

Now connect via a browser to the to the IP address of your SBC and test each button
to verify the SBC is correctly configured.

Test that all services are running:

```
pgrep aria2
pgrep smb
pgrep dlna
pgrep noip2
pgrep btmain
cat /var/log/messages | grep rc.local
```

Test logrotation with command:

```
logrotate -f /etc/logrotate.conf
```


## CLEANUP ##

```
sudo apt-get --yes autoremove
sudo apt-get --yes autoclean
sudo apt-get --yes clean
```

## BACKUP ##

```
apt-get install -y pv
dd if=/dev/mmcblk0 | pv -s 4G -peta | gzip -1 > /media/extdisc/backup-LMC-$(date +%F)-working.img.gz
```

