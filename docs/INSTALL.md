# Light Media Center Installation Guide #

The installation steps are divided in the steps required to configure some system services (like LAN/Internet connectivity, email server, Samba sharing, etc) and those required to instead download/build/configure LightMediaCenter-specific softwares.

Beware that these guides assume that you know what you are doing. They should be considered as handy HOW-TO guides for expert users only. 


## Hardware Required ##

This guide provides installation steps assuming you are using Debian GNU/Linux 8.1 (jessie) distribution on <a href="https://www.olimex.com/Products/OLinuXino/A20/A20-OLinuXIno-LIME2/">OLinuxino A20 LIME2</a>.
However, it contains also references and some notes that apply to other HW / SW combinations like:
- BeagleBone with Ubuntu
- Raspberry PI model B with Raspbian
Later in this guide, such hardware will be referred to as "the single-board computer (SBC)".

This guide assumes that you have, as hardware:
- Internet connectivity on the SBC
- An external hard drive, connected to the SBC either by USB or by SATA (in case of OLinuxino A20 LIME2)


## Suggested Software ##

http://www.armbian.com/

http://www.armbian.com/olimex-lime-2/


## Configuration of Default System Packages ##

See  <a href="INSTALL.SYSTEM.md">INSTALL.SYSTEM.md</a>.


## Build/Configuration of Media Center Software ##

See  <a href="INSTALL.MEDIACENTER.md">INSTALL.MEDIACENTER.md</a>.


