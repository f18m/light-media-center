# Light Media Center Installation Guide #

This guide provides installation steps assuming you are using a Debian Linux distribution on <a href="https://www.olimex.com/Products/OLinuXino/A20/A20-OLinuXIno-LIME2/">OLinuxino A20 LIME2</a>.
Note that all install procedures that are easy and safe to automate have been included in the Light Media Center makefile as install-SOMETHING targets; however tasks that are non-trivial to automate or strongly dependent upon the specific Linux distribution version or 3rd party software versions are instead listed here as manual steps.


## Download of Light Media Center ##

```
cd /opt
git clone https://github.com/f18m/light-media-center.git
make download-aux
make install-links
make install-cron
make install-initd
make install-logrotate
make install-email-on-boot
```

## Configure bash aliases ##

```
rm .bashrc && wget http://frm.users.sourceforge.net/macros/.bashrc
bash
```

## Change system host name ##

```
sudo nano /etc/hostname
```

Put "OLinuxino", this will fix the appearance on Samba networks!

```
sudo nano /etc/hosts
reboot
```


## Ensure debian user exists ##

This guide makes the assumption that a "debian" user exists on the system.
All software that does not need to run as root is run as "debian", so make sure it exists
(you can check in /etc/passwd) or otherwise add it:

```
apt-get install passwd
useradd debian
```



## Ensure required native software is installed ##

```
sudo apt-get install ntfs-3g build-essential
```

### Raspberry-specific note ###
To disable RASPBMC native AUTOMOUNT just:
```
   nano /etc/udisks-glue.conf
```
and change "automount = true" to "automount = false"



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

## Configure MINIDLNA stuff ##

```
wget http://sourceforge.net/projects/minidlna/files/latest/download
tar -xvzf download
rm download

cd minidlna-1.1.4/
apt-get install libavformat-dev libavutil-dev libavcodec-dev libflac-dev libvorbis-dev libid3tag0-dev libexif-dev libjpeg-dev libsqlite3-dev libogg-dev 
./configure
make
make install-strip

echo >/var/log/minidlna.log
chown -R pi:pi /var/lib/minidlna /var/log/minidlna.log
```

Then allow minidlna to monitor many files:

```
nano /etc/sysctl.conf 
```

------------------ cut here ----------------------
```
# for miniDLNA:
fs.inotify.max_user_watches=163840
```
------------------ cut here ----------------------


Now make minidlna scan the external disk:

```
nano /etc/minidlna.conf
```

------------------ cut here ----------------------
```
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
```
------------------ cut here ----------------------


### Gotchas ###

Adding "minidlnad -u pi" to the rc.local file will not work; it must be added to the if-up scripts instead!


 
 
 
## Configure NO-IP stuff ##

From http://www.noip.com/support/knowledgebase/installing-the-linux-dynamic-update-client/

```
cd /usr/local/src
wget http://www.no-ip.com/client/linux/noip-duc-linux.tar.gz
tar xzf noip-duc-linux.tar.gz && cd no-ip-2.1.9
make && make install
```



## Configure WEB INTERFACE (IN LIGHTTPD) ##

VIA THE GRAPHICAL USER INTERFACE, DISABLE KODI WEBSERVER:

 Settings → Services → Webserver → Allow control of XBMC/Kodi via HTTP
 
```
apt-get install lighttpd

# the apache user www-data must be in the pi group:
/usr/sbin/usermod -a -G pi www-data

# ensure that /var/www/* are read/write for pi group:
chown -R pi:pi /var/www
chmod -R ug+rw /var/www
```

```
  // IMPORTANT: make sure that the www-data user is enabled to elevate to root permissions;
  //            this is very unsecure but it is quick to setup; in /etc/sudoers write:
  //                  sudo visudo
  //                  www-data ALL=(ALL) NOPASSWD: ALL

  
sudo apt-get install php5-common php5-cgi php5

 /usr/sbin/lighty-enable-mod fastcgi
 /usr/sbin/lighty-enable-mod fastcgi-php
 /usr/sbin/lighty-enable-mod auth
 
nano /etc/lighttpd/conf-enabled/15-fastcgi-php.conf
```

set:

------------------ cut here ----------------------
```
                        "PHP_FCGI_CHILDREN" => "2",
```
------------------ cut here ----------------------

to save memory.

```
service lighttpd restart
```

# then create the symlink in the right place:

```
cd /var/www/html
ln -s /media/extdiscMAIN extdiscMAIN
ln -s /media/extdiscTORRENTS extdiscTORRENTS
```


## Configure ARIA2 ##

Note that packaged aria2 in Debian sid is too old (version 1.15.1 currently) so it's best to recompile it:

```
wget https://github.com/tatsuhiro-t/aria2/archive/release-1.19.3.tar.gz
tar -xvzf release-1.19.3.tar.gz
rm release-1.19.3.tar.gz

cd aria2-1.19.3

apt-get install libxml2-dev nettle-dev libssl-dev libgcrypt-dev libgnutls-dev

./configure --prefix=/usr
```

verify the output:

```
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
```

```
make && make install-strip
# go take a coffeee!!! takes >1h
```

As of aria2 1.19.3, aria2 does not come with a default etc file, so a default one is included in Light Media Center sources:

```
cd /opt/light-media-center/etc/aria2.conf /etc

echo >/var/log/aria2.log
chown -R pi:pi /home/pi/.aria2 /home/pi/aria2hooks /home/pi/aria2utils /var/log/aria2.log
chmod -R ug+rw /home/pi/.aria2 /home/pi/aria2hooks /home/pi/aria2utils /var/log/aria2.log

/etc/init.d/aria2 start
```

Use aria2q utility to verify it's working:

```
aria2q
```

Then modify RPC port and password if needed and set them in the webui-aria2 config file:

```
nano /var/www/html/webui-aria2/configuration.js
```



### Configure MLDONKEY ##

```
apt-get install mldonkey-server telnet
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

from other terminal

```
   $ telnet 127.0.0.1 4000
   > auth admin ""
   > passwd deskjet23
   > set allowed_ips 255.255.255.255
   > quit
```

Then open everywhere in your LAN the port 4080
   
   
## Configure SSMTP (only if you want to receive mail notifications) ##

```
apt-get install ssmtp
nano /etc/ssmtp/ssmtp.conf
```

------------------ cut here ----------------------
```
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
```
------------------ cut here ----------------------


```
chmod 640 /etc/ssmtp/ssmtp.conf
chown root:mail /etc/ssmtp/ssmtp.conf

gpasswd -a root mail
gpasswd -a debian mail

nano /etc/ssmtp/revaliases
```

------------------ cut here ----------------------
```
root:OLinuxino_admin@gmail.com:smtp.gmail.com:587
debian:OLinuxino_debian@gmail.com:smtp.gmail.com:587
```
------------------ cut here ----------------------


```
apt-get install mailutils
echo test | mail -s "testing ssmtp setup" your.email@gmail.com
```



## Configure UPRECORDS ##

```
apt-get install uptimed
```


## Configure SECURITY ##

```
nano /etc/ssh/sshd_config
change "Port 22" -> "Port 512"
/etc/init.d/ssh restart
```

AFTER changing the SSH port number, install fail2ban:

```
apt-get install fail2ban
chown debian:debian /var/log/fail2ban.log
nano /etc/fail2ban/jail.conf
```

Change "port     = ssh" with "port     = ssh,512"

```
/etc/init.d/fail2ban restart
```

## FINAL CHECKS ##

```
reboot
```

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


## BACKUP ##

```
apt-get install pv
dd if=/dev/mmcblk0 | pv -s 4G -peta | gzip -1 > /media/extdiscMAIN/backup-OLinuxino-13apr2014-working-debian.img.gz
```

