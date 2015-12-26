# Light Media Center

A small fast collection of scripts to setup your personal home media center.

Some features of the media center: 

0. Has a web interface built around open-source components (h5ai, webui-aria2, yaaw, etc)
0  Responsive web design (thanks to Skeleton) for use by smartphones/tablets
0. Tested on Beaglebone, Raspberry and OLinuxino

# How to Install

```

# first install a very simple web portal to your HTTP server root folder:
cd /var/www
git clone https://github.com/f18m/light-media-center.git   html

# install auxiliary software:
mkdir webui-aria2 && cd webui-aria2 && git clone https://github.com/ziahamza/webui-aria2.git
mkdir yaaw && cd yaaw && git clone https://github.com/binux/yaaw.git
mkdir _h5ai && cd _h5ai && wget https://release.larsjung.de/h5ai/h5ai-0.28.1.zip && unzip h5ai-0.28.1.zip


```


