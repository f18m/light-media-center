# Configuration of System Packages #

## 1) Configure bash aliases ##

```
rm .bashrc && wget http://frm.users.sourceforge.net/macros/.bashrc
bash
```

## 2) Change system host name ##

```
sudo nano /etc/hostname
```

Put "OLinuxino", this will fix the appearance on Samba networks!

```
sudo nano /etc/hosts
reboot
```


## 3) Ensure debian user exists ##

This guide makes the assumption that a "debian" user exists on the system.
All software that does not need to run as root is run as "debian", so make sure it exists
(you can check in /etc/passwd) or otherwise add it:

```
apt-get install passwd
useradd debian
```


## 4) Ensure required native software is installed ##

```
sudo apt-get install ntfs-3g build-essential
```

### Raspberry-specific note ###
To disable RASPBMC native AUTOMOUNT just:
```
   nano /etc/udisks-glue.conf
```
and change "automount = true" to "automount = false"



## 5) Configure networking ##

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
 
or use NetworkManager CLI if the single-board computer distribution is
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

 
 
## 6) Configure SAMBA sharing ##

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

   
## 7) Configure SSMTP ##

This step is useful only if you want to receive mail notifications

```
apt-get install ssmtp
nano /etc/ssmtp/ssmtp.conf
```

------------------ cut here ----------------------
```
# The user that gets all the mails (UID < 1000, usually the admin)
root=YOUR_MAIL@gmail.com

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
AuthUser=YOUR_MAIL
AuthPass=YOUR_PASSWORD

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


## 8) Configure UPRECORDS ##

```
apt-get install uptimed
```


## 9) Configure SECURITY ##

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
