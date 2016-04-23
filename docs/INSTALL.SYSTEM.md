# Configuration of System Packages #

This guide provides installation steps assuming you are using Debian GNU/Linux 8.1 (jessie) distribution on <a href="https://www.olimex.com/Products/OLinuXino/A20/A20-OLinuXIno-LIME2/">OLinuxino A20 LIME2</a>.
However, it contains also references and some notes that apply to other HW / SW combinations like:
- BeagleBone with Ubuntu
- Raspberry PI model B with Raspbian / KODI
Other untested combinations _should_ work, too...

Note that generally speaking all the following configurations and commands must be run as root.


## 0) Partitioning ##



## 1) Configure networking ##

Edit /etc/network/interfaces to have a static IP on your LAN.
The following IP addresses are just a dummy example, change them to match
your LAN subnet:

```
nano /etc/network/interfaces

------------------ cut here ----------------------
auto eth0
iface eth0 inet static
    address 192.168.2.99
    netmask 255.255.255.0
    network 192.168.2.0
    gateway 192.168.2.1
    dns-nameservers 8.8.8.8
------------------ cut here ----------------------
```
 
or use NetworkManager CLI if your single-board computer Linux distribution is
using that:

```
nmcli con edit
[add new ethernet connection]
set ipv4.addresses 192.168.2.99/24
set ipv4.gateway 192.168.2.1
set ipv4.dns 8.8.8.8 8.8.4.4
print
save
```

## 2) Configure bash aliases ##

```
rm .bashrc && wget http://frm.users.sourceforge.net/macros/.bashrc
bash
```

## 3) Change system host name ##

```
nano /etc/hostname
```

Choose your host name to something you like. Note that hostnames longer than 15 characters in my
experience lead to troubles when trying to logging via Samba from Windows computers, so my suggestion 
is to keep the hostname shorter than 15 characters!

Then add the new hostname also in /etc/hosts:

```
nano /etc/hosts
```

In particular ensure that the line beginning with
127.0.0.1 resolves first to the new chosen hostname. E.g., if you chose "LightMC" as hostname your
/etc/hosts file should start with "127.0.0.1       LightMC localhost"

Finally reboot and verify the new hostname is appearing at BASH prompt:

```
reboot
```


## 4) Configure the time zone ##

Run 
```
date
```

and check if the time zone is already ok. If it's not, then you can check which timezones exist:

```
ls -l /usr/share/zoneinfo/Europe/
```

and modify accordingly to where you live. Note that changing the timezone seems to be highly 
distribution-specific. For example :

```
dpkg-reconfigure tzdata
reboot
```


## 5) Ensure debian user exists ##

This guide makes the assumption that a "debian" user exists on the system.
All software that does not need to run as root is run as "debian", so make sure it exists
(you can check in /etc/passwd) or otherwise add it:

```
apt-get install passwd
adduser --home /home/debian debian
```


## 6) Ensure required native software is installed ##

```
apt-get install ntfs-3g build-essential
```

### Raspberry-specific note ###
To disable RASPBMC native AUTOMOUNT just:
```
nano /etc/udisks-glue.conf
```
and change "automount = true" to "automount = false"

 
 
## 7) Configure SAMBA sharing ##

```
apt-get install samba smbclient
nano /etc/samba/smb.conf 

------------------ cut here ----------------------
# add a [extdisc] section allowing to browse to /media/extdisc:

[extdisc]
  comment = External disk directory
  path = /media/extdisc
  valid users = debian
  public = no
  writable = yes
  browseable = yes

------------------ cut here ----------------------

mkdir -p /media/extdisc
testparm
smbpasswd -a debian
service samba restart
```

If the last line does not work:

```
service smbd restart
service nmbd restart
```

Then to verify the user debian was correctly registered:
```
pdbedit -L -v
```

smbclient -L LIGHTMEDIACENTE



## 8) Configure SSMTP ##

This step is useful only if you want to receive mail notifications

```
apt-get install ssmtp mailutils
nano /etc/ssmtp/ssmtp.conf

------------------ cut here ----------------------
# The user that gets all the mails (UID < 1000, usually the admin)
root=YOUR_MAIL@gmail.com

# The mail server (where the mail is sent to), both port 465 or 587 should be acceptable
# See also http://mail.google.com/support/bin/answer.py?answer=78799
mailhub=smtp.gmail.com:587

# The address where the mail appears to come from for user authentication.
rewriteDomain=gmail.com

# The full hostname
hostname=LightMC

# Use SSL/TLS before starting negotiation
UseTLS=Yes
UseSTARTTLS=Yes

# Username/Password
AuthUser=YOUR_MAIL
AuthPass=YOUR_PASSWORD

# Email 'From header's can override the default domain?
FromLineOverride=yes
------------------ cut here ----------------------
```

Now secure your configuration file:

```
groupadd ssmtp
chown :ssmtp /etc/ssmtp/ssmtp.conf
chmod 640 /etc/ssmtp/ssmtp.conf

chown :ssmtp /usr/sbin/ssmtp
chmod g+s /usr/sbin/ssmtp

gpasswd -a root mail

nano /etc/ssmtp/revaliases

------------------ cut here ----------------------
root:LightMC_admin@gmail.com:smtp.gmail.com:587
debian:LightMC_debian@gmail.com:smtp.gmail.com:587
------------------ cut here ----------------------
```

Now log as "debian" user on the system and verify that you can send emails:

```
echo test | mail -s "testing ssmtp setup" your.email@gmail.com
```

(verify that your Gmail mailbox has received the test email).
Also verify that your SSMTP configuration file is protected:

```
cat /etc/ssmtp/ssmtp.conf
```

This should fail with "permission denied".



## 9) Disable Eventually-Present Display Managers ##

Depending on the way you will be using your SBC with Light Media Center you may not need
any graphical system producing outputs on the SBC HDMI port. For this reason you can save CPU and memory
by disabling desktop managers. 
This step is highly distribution-specific. My OLinuxino with Debian Jessie came with lightdm enabled on boot.
To disable it:

```
systemctl disable lightdm
```


## 10) Configure SECURITY ##

To increase security of the Media Center, you may want to change 
the port for the SSH server from the standard port 22 to another port.

```
nano /etc/ssh/sshd_config
[change "Port 22" key]
/etc/init.d/sshd restart
```

Another tool that may prove useful to automatically ban IP addresses that show
malicious signs -- too many password failures, seeking for exploits, etc -- 
is the "fail2ban" utility (http://www.fail2ban.org/):

```
apt-get install fail2ban
chown debian:debian /var/log/fail2ban.log
```

In this case SSH server port was changed before, it also needs to be
updated in the fail2ban configuration file:

```
nano /etc/fail2ban/jail.conf
[ Change "port     = ssh" with "port     = ssh,NNN" where NNN is the new port number]
/etc/init.d/fail2ban restart
```

Finally just ensure that you have a strong password for the root user
(this also helps protecting the clear-text password for your email address that you
stored in the /etc/ssmtp/ssmtp.conf file!!):

```
passwd
```

## 11) Reduce write activities to your SD card ##

To help your SD card / NAND FLASH memory to survive to long years of loyal service, you should
try to decrease as much as possible writes to the filesystem stored on such memories. 
To do this, I suggest following one of the following guides:

- http://www.richardsramblings.com/2013/02/extend-the-life-of-your-rpi-sd-card/

Note that to have /tmp as a RAM filesystem I had to first manually download the file "tmp.mount"
from "systemd" debian package into "/lib/systemd/system/" (for some reason it was missing) and then run:
```
systemctl enable tmp.mount
```

My iostats on OLinuXino A20-OLinuXIno-LIME2 with Debian Jessie 8.1 before optimizations were:

```
$ iostat -d 300 3

Linux 3.4.103-00033-g9a1cd03-dirty (LightMC)    01/06/16        _armv7l_        (2 CPU)

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.01         0.06         0.01       1357        172
mmcblk0           0.37         5.83         1.82     143350      44724

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.00         0.00         0.00          0          0
mmcblk0           0.43         0.00         2.81          0        844

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.00         0.00         0.00          0          0
mmcblk0           0.12         0.03         0.85          8        256
```

showing up to 844+256 KBs written in 10 minutes. After some basic optimizations:

```
$ iostat -d 300 3

Linux 3.4.103-00033-g9a1cd03-dirty (LightMC)    01/06/16        _armv7l_        (2 CPU)
Linux 3.4.103-00033-g9a1cd03-dirty (LightMC)    01/06/16        _armv7l_

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.64         3.53         0.41       1337        156
mmcblk0           8.92       240.98         5.89      91282       2232

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.00         0.00         0.00          0          0
mmcblk0           0.46         4.91         1.97       1472        592

Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
sda               0.00         0.00         0.00          0          0
mmcblk0           0.35        15.47         0.91       4640        272
```

Another command useful to identify top-writer programs is:

```
iotop -o -P -b -n 2 -d 300
```

In my case I did:
- mount root filesystem with noatime and commit every 60sec:

```
$ mount
/dev/mmcblk0p2 on / type ext4 (rw,noatime,nodiratime,commit=60,data=ordered)
...
```

- enabled /tmp mount point in RAM:

```
$ mount
...
tmpfs on /tmp type tmpfs (rw)
...
```

- removed unwanted entries from /etc/cron.d

