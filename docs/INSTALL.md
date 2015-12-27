# Light Media Center Installation Guide #

This guide provides installation steps assuming you are using a Debian Linux distribution on <a href="https://www.olimex.com/Products/OLinuXino/A20/A20-OLinuXIno-LIME2/">OLinuxino A20 LIME2</a>.



## Download of Light Media Center ##

```
cd /opt
git clone https://github.com/f18m/light-media-center.git
make download-aux
make install-links
make install-cron
```

## Configure bash aliases ##

```
rm .bashrc && wget http://frm.users.sourceforge.net/macros/.bashrc
bash
```

## Configure networking ##

edit /etc/network/interfaces to have a static IP

------------------ cut here ----------------------
```
iface eth0 inet static
    address 192.168.2.98
    netmask 255.255.255.0
    network 192.168.2.0
    gateway 192.168.2.1
```
------------------ cut here ----------------------
 
 
## Configure SAMBA sharing ##

```
sudo nano /etc/samba/smb.conf 
```

------------------ cut here ----------------------
```
# add a [extdisc] section allowing to browse to /media/extdisc:

[extdiscMAIN]
  comment = External disk directory
  path = /media/extdiscMAIN
  valid users = pi
  public = no
  writable = yes
  browseable = yes

[extdiscMAIN2]
  comment = External disk directory
  path = /media/extdiscMAIN2
  valid users = pi
  public = no
  writable = yes
  browseable = yes
  
[extdiscTORRENTS]
  comment = External disk directory
  path = /media/extdiscTORRENTS
  valid users = pi
  public = no
  writable = yes
  browseable = yes
```
------------------ cut here ----------------------

```
testparm
service samba restart
```

################################## configure MINIDLNA stuff

############apt-get install minidlna    #if not available, install from sourceforge

# NOTE: adding 
#			minidlnad -u pi
#		to the rc.local file will not work; it must be added to the if-up scripts instead!

wget http://sourceforge.net/projects/minidlna/files/latest/download
tar -xvzf download
rm download

cd minidlna-1.1.4/
apt-get install libavformat-dev libavutil-dev libavcodec-dev libflac-dev libvorbis-dev libid3tag0-dev libexif-dev libjpeg-dev libsqlite3-dev libogg-dev 
./configure
make
make install-strip


# allow minidlna to monitor many files:

nano /etc/sysctl.conf 

------------------ cut here ----------------------
# for miniDLNA:
fs.inotify.max_user_watches=163840
------------------ cut here ----------------------


# make minidlna scan the external disk:

nano /etc/minidlna.conf



------------------ cut here ----------------------

# specify the user account name or uid to run as
user=ubuntu

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
friendly_name=OLinuxino

# Path to the directory that should hold the database and album art cache.
db_dir=/var/lib/minidlna

# Path to the directory that should hold the log file.
log_dir=/var/log
------------------ cut here ----------------------


echo>/var/log/minidlna.log
chown -R pi:pi /var/lib/minidlna /var/log/minidlna.log




################################# configure AUTOMOUNT stuff

# install NTFS write support:
sudo apt-get install ntfs-3g

1) copy "btmain.sh" to /usr/local/bin
2) copy btmain init script to /etc/init.d

3)

   chmod a+x /usr/local/bin/btmain.sh  /etc/init.d/btmain

3) update-rc.d btmain defaults



4) to disable RASPBMC native AUTOMOUNT DO:              [UNNECESSARY]
   nano /etc/udisks-glue.conf
change
   automount = true
to
   automount = false

   
5) finally to configure log rotation for btmain: create the file "btmain"
   in /etc/logrotate.d:
   
------------------ cut here ----------------------

/var/log/bt*.log {
        weekly
        missingok
# keep 3 weeks worth of backlogs
        rotate 3
# create new (empty) log files after rotating old ones
        create
        delaycompress
        compress
        notifempty
}

------------------ cut here ----------------------

test with command:

 logrotate -f /etc/logrotate.conf

 
 
 
################################# configure NO-IP stuff

# install gcc

sudo apt-get install build-essential


# from http://www.noip.com/support/knowledgebase/installing-the-linux-dynamic-update-client/

cd /usr/local/src
wget http://www.no-ip.com/client/linux/noip-duc-linux.tar.gz
tar xzf noip-duc-linux.tar.gz && cd no-ip-2.1.9
make && make install



copy noip2 into /etc/init.d/

update-rc.d noip2 defaults



################################## configure WEB INTERFACE (IN LIGHTTPD)

VIA THE GRAPHICAL USER INTERFACE, DISABLE KODI WEBSERVER:

 Settings → Services → Webserver → Allow control of XBMC/Kodi via HTTP
 
 
apt-get install lighttpd


# copy my .php stuff to the /var/www folder, then:

# the apache user www-data must be in the pi group:
/usr/sbin/usermod -a -G pi www-data

# ensure that /var/www/* are read/write for pi group:
chown -R pi:pi /var/www
chmod -R ug+rw /var/www


  // IMPORTANT: make sure that the www-data user is enabled to elevate to root permissions;
  //            this is very unsecure but it is quick to setup; in /etc/sudoers write:
  //                  sudo visudo
  //                  www-data ALL=(ALL) NOPASSWD: ALL

  
sudo apt-get install php5-common php5-cgi php5

 /usr/sbin/lighty-enable-mod fastcgi
 /usr/sbin/lighty-enable-mod fastcgi-php
 /usr/sbin/lighty-enable-mod auth
 
 
nano /etc/lighttpd/conf-enabled/15-fastcgi-php.conf
set:

------------------ cut here ----------------------
                        "PHP_FCGI_CHILDREN" => "2",
------------------ cut here ----------------------

to save memory.

service lighttpd restart


# then create the symlink in the right place:

cd /var/www/html
ln -s /media/extdiscMAIN extdiscMAIN
ln -s /media/extdiscTORRENTS extdiscTORRENTS



################################## configure ARIA2


#apt-get install aria2               # too old ---- version 1.15.1 currently!!!! (OUCH)


wget https://github.com/tatsuhiro-t/aria2/archive/release-1.19.3.tar.gz
tar -xvzf release-1.19.3.tar.gz
rm release-1.19.3.tar.gz

cd aria2-1.19.3

apt-get install libxml2-dev nettle-dev libssl-dev libgcrypt-dev libgnutls-dev

./configure --prefix=/usr

verify the output:

Build:          armv6l-unknown-linux-gnueabihf
Host:           armv6l-unknown-linux-gnueabihf
Target:         armv6l-unknown-linux-gnueabihf
Install prefix: /usr
CC:             gcc
CXX:            g++
CPP:            gcc -E
CXXFLAGS:       -g -O2 -pipe -std=c++0x
CFLAGS:         -g -O2 -pipe
CPPFLAGS:       -I$(top_builddir)/deps/wslay/lib/includes -I$(top_srcdir)/deps/wslay/lib/includes -I/usr/include/libxml2
LDFLAGS:
LIBS:           -lrt -lgmp -lnettle -L/usr/lib -lxml2 -lz
DEFS:           -DHAVE_CONFIG_H
LibUV:          no
SQLite3:        no
SSL Support:    no
AppleTLS:
WinTLS:         no
GnuTLS:         no
OpenSSL:        no
CA Bundle:
LibXML2:        yes
LibExpat:
LibCares:       no
Zlib:           yes
Epoll:          yes
Bittorrent:     yes
Metalink:       yes
XML-RPC:        yes
Message Digest: libnettle
WebSocket:      yes
Libaria2:       no
bash_completion dir: ${datarootdir}/do


make && make install-strip

# go take a coffeee!!! takes >1h

copy in /etc  the file aria2.conf
copy in /etc/init.d  the file aria2
copy in /home/pi aria2hooks & aria2utils

echo>/var/log/aria2.log
chown -R pi:pi /home/pi/.aria2 /home/pi/aria2hooks /home/pi/aria2utils /var/log/aria2.log
chmod -R ug+rw /home/pi/.aria2 /home/pi/aria2hooks /home/pi/aria2utils /var/log/aria2.log

cd /home/pi/aria2utils && ./install.sh

/etc/init.d/aria2 start

aria2q to verify it's working


cd /var/www/html/webui-aria2

apt-get install git
git clone https://github.com/ziahamza/webui-aria2.git

nano /var/www/html/webui-aria2/configuration.js

reboot

################################## configure MLDONKEY

apt-get install mldonkey-server telnet

in /etc/default/mldonkey-server

------------------ cut here ----------------------
    MLDONKEY_USER=debian
    MLDONKEY_GROUP=debian
------------------ cut here ----------------------

then

   su debian
   mlnet

from other terminal

   $ telnet 127.0.0.1 4000
   > auth admin ""
   > passwd deskjet23
   > set allowed_ips 255.255.255.255
   > quit

open everywhere the port 4080
   
   
################################## configure SSMTP (to receive mail notifications!)

apt-get install ssmtp

nano /etc/ssmtp/ssmtp.conf

------------------ cut here ----------------------

# The user that gets all the mails (UID < 1000, usually the admin)
root=francesco.montorsi@gmail.com

# The mail server (where the mail is sent to), both port 465 or 587 should be acceptable
# See also http://mail.google.com/support/bin/answer.py?answer=78799
mailhub=smtp.gmail.com:587

# The address where the mail appears to come from for user authentication.
rewriteDomain=gmail.com

# The full hostname
hostname=OLinuxino

# Use SSL/TLS before starting negotiation
UseTLS=Yes
UseSTARTTLS=Yes

# Username/Password
AuthUser=francesco.montorsi
AuthPass=VJII28_234

# Email 'From header's can override the default domain?
FromLineOverride=yes
------------------ cut here ----------------------


chmod 640 /etc/ssmtp/ssmtp.conf
chown root:mail /etc/ssmtp/ssmtp.conf

gpasswd -a root mail
gpasswd -a debian mail

nano /etc/ssmtp/revaliases

------------------ cut here ----------------------
root:OLinuxino_admin@gmail.com:smtp.gmail.com:587
debian:OLinuxino_debian@gmail.com:smtp.gmail.com:587
------------------ cut here ----------------------



apt-get install mailutils

echo test | mail -s "testing ssmtp setup" francesco.montorsi@gmail.com


copy emailnotify.sh to /usr/local/bin/
chmod a+x /usr/local/bin/emailnotify.sh
nano /etc/rc.local

------------------ cut here ----------------------
/usr/local/bin/emailnotify.sh &
------------------ cut here ----------------------



   
################################## configure WATCHDOG

update-rc.d btwatchdog defaults



################################## configure UPRECORDS


apt-get install uptimed



################################## change HOSTNAME

sudo nano /etc/hostname

# put "OLinuxino", this will fix the appearance on Samba networks!

sudo nano /etc/hosts

reboot


################################## configure SECURITY



nano /etc/ssh/sshd_config
change "Port 22" -> "Port 512"
/etc/init.d/ssh restart

# AFTER changing the SSH port number install fail2ban:
apt-get install fail2ban
chown debian:debian /var/log/fail2ban.log

nano /etc/fail2ban/jail.conf
Change "port     = ssh" with "port     = ssh,512"
/etc/init.d/fail2ban restart






################################# FINAL CHECKS

reboot

# test that all services are running:

pgrep aria2
pgrep smb
pgrep dlna
pgrep noip2
pgrep btmain
####cat /var/log/messages | grep rc.local



################################# BACKUP

apt-get install pv
dd if=/dev/mmcblk0 | pv -s 4G -peta | gzip -1 > /media/extdiscMAIN/backup-OLinuxino-13apr2014-working-debian.img.gz


