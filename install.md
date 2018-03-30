Virtualbox
------------

Installing VirtualBox and FreeBSD

* Download FreeBSD ISO: [ftp://ftp2.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso](ftp://ftp2.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso)

* Download VirtualBox from [virtualbox.org](https://www.virtualbox.org/wiki/Downloads)


Install the app, you know, continue, continue, agree, continue, install etc.

[Detailed step by step instructions for installing virtualbox](install-virtualbox.md)

Installation
------------

[Detailed step by step instructions for installing FreeBSD in the Virtualbox VM](install-freebsd.md)


Configuration in freebsd
------------
We will continue in Terminal, ssh.

```
ssh openvpn@ip
su
# Enter password used for root
freebsd-update fetch install
# Press enter for more and then q for quit.
pkg install -y nano screen bash git openvpn apache24 php56-openssl php56-session php56-gettext mod_php56
bash
```

Configure freebsd
```
nano /etc/rc.conf
```

Add the following at the bottom:

```
apache24_enable="YES"
firewall_enable="YES"
firewall_type="open"

gateway_enable="YES"
natd_enable="YES"
# If ESXi adjust to vmx0 - you should have the interface name noted. Use it here.
natd_interface="em0"
natd_flags="-dynamic -m -interface vmx0"

openvpn_enable="YES"
openvpn_configfile="/usr/local/etc/openvpn/server.conf"
```

## Sync current time

Add synchronizing the date and time in the crontab.

```
ntpdate ntp1.sth.netnod.se
nano /etc/crontab
00      *       *       *       *       root    ntpdate -s ntp1.sth.netnod.se
```

## Download git repo

```
rm -fr /usr/local/www/apache24/data/
git clone https://github.com/nblom/openvpn-php.git /usr/local/www/apache24/data/
chmod 777 /usr/local/www/apache24/data/
```

## Configuring Apache
```
cp /usr/local/www/apache24/data/apache.conf /usr/local/etc/apache24/Includes/apache.conf
service apache24 start
```

## Configure OpenVPN
```
mkdir /usr/local/etc/openvpn/
cp /usr/local/www/apache24/data/server.conf /usr/local/etc/openvpn/
openssl dhparam -out /usr/local/etc/openvpn/dh.pem 2048
```

Select language for OpenVPN-PHP
```
nano /usr/local/www/apache24/data/config.php
```


Call index.php via curl (to create the right files with the proper permissions)

```
curl localhost
```
### Save password generated!

Move the OpenVPN files and copy about your certificate.

```
mv /usr/local/www/apache24/data/openvpn-server.* /usr/local/etc/openvpn/
cp /usr/local/www/apache24/data/ca.crt /usr/local/etc/openvpn/
chmod 600 /usr/local/etc/openvpn/openvpn-server.key
chown root /usr/local/etc/openvpn/openvpn-server.key
```

Now you can copy the apachessl config, so after reboot it will listen to https with your certficate.

```
cp /usr/local/www/apache24/data/apachessl.conf /usr/local/etc/apache24/Includes/apachessl.conf
```

configure OpenVPN

Adjust push and or server directives
```
nano /usr/local/etc/openvpn/server.conf
```


And finally, reboot the entire machine.

```
reboot
or
shutdown -h now # if you want to use LaunchDaemon to start Virtualbox headless.
```

Visiting the webgui
------------
Point your browser to https://**your internal ip**

You should see this site, after an alert of course that your certificate is completely inaccurate.

![Login screnshot](images/openvpn-php1.png?raw=true "Login")



Firewall
------------

To OpenVPN to work you need to map the external IP to internally **UDP port 1194**

Examples for Halon securityrouter.org:

```
pass in quick on wan proto udp to (wan) port 1194 rdr-to 192.168.0.x label OpenVPN
```


If you want to start virtualbox headless.
------------

As root in the Terminal, check so root has registered the VM, otherwise you need to register it.

```
sudo -s
/usr/local/bin/VBoxManage list vms
/usr/local/bin/VBoxManage registervm <path to OpenVPN.vbox>
```

![Virtualbox screenshot](images/virtualbox30.png?raw=true)

LaunchDaemon file for automatic start headless:

```
sudo nano /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
sudo chmod 644 /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
sudo launchctl load /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
```

Change the name if you have not named the machine to "OpenVPN"

```
<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>Label</key>
	<string>se.lop.virtualbox.openvpn</string>
	<key>ThrottleInterval</key>
        <string>120</string>
	<key>UserName</key>
	<string>root</string>
	<key>GroupName</key>
	<string>wheel</string>
	<key>Nice</key>
	<integer>10</integer>
	<key>RunAtLoad</key>
	<true/>
	<key>KeepAlive</key>
	<true/>
	<key>ProgramArguments</key>
	<array>
		<string>/usr/local/bin/VBoxManage</string>
        	<string>startvm</string>
        	<string>OpenVPN</string>
        	<string>--type</string>
        	<string>headless</string>
	</array>
</dict>
</plist>
```

Apple Remote Desktop deployment of Tunnelblick
------------

Click on [Download Latest Stable Release](https://tunnelblick.net/) and mount the downloaded DMG.

Copy Tunnelblick Application to target clients Application folder.

![List screnshot](images/remotedesktop1.png?raw=true "List of users")

Send Unix Command *note the escaped space in the path*

```
mkdir -p ~/Library/Application\ Support/Tunnelblick/Configurations
```

![List screnshot](images/remotedesktop2.png?raw=true "List of users")

Copy configuration from downloaded webgui *note no escaped space in the Place item in path*

```
~/Library/Application Support/Tunnelblick/Configurations/
```

![List screnshot](images/remotedesktop3.png?raw=true "List of users")

Rename configuration to something more user friendly

```
mv ~/Library/Application\ Support/Tunnelblick/Configurations/*.ovpn ~/Library/Application\ Support/Tunnelblick/Configurations/CompanyName.ovpn
```

![List screnshot](images/remotedesktop4.png?raw=true "List of users")



Tunnelblick configuration on macOS client
------------

Launch the Tunnelblick app and Click Convert Configurations

![List screnshot](images/tunnelblick1.png?raw=true "Tunnelblick screenshot")

Click do not check for change

![List screnshot](images/tunnelblick2.png?raw=true "Tunnelblick screenshot")

Check for updates if your want.

![List screnshot](images/tunnelblick3.png?raw=true "Tunnelblick screenshot")

Place the icon where you want.

![List screnshot](images/tunnelblick4.png?raw=true "Tunnelblick screenshot")

Select VPN Details...

![List screnshot](images/tunnelblick5.png?raw=true "Tunnelblick screenshot")

Select *Do not set nameservers* under Set DNS.

Also recommend to deselect *Check if apparent public IP address changed after connecting.*

![List screnshot](images/tunnelblick6.png?raw=true "Tunnelblick screenshot")


To change IP
------------

1) edit /etc/rc.conf
2) edit /usr/local/openvpn/server.conf