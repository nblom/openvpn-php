Virtualbox
------------

Installing VirtualBox and create a new FreeBSD 64 machine as:

Retrieve [ftp://ftp.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso](ftp://ftp.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso)


* 1 CPU
* 1 GB of RAM
* 10 GB HDD
* Change from NAT networking to Bridge.
* Mount the ISO in the virtual CD.
* Start the virtual machine.

Installation
------------
When you boot FreeBSD from the CD, select the following:

![freebsd00]

Choose a keyboard that suits you.

![freebsd01]

Choose a hostname that you will point to the DNS. **Do not forget to point hosname against external IP in dnsen**, do it now, just to be sure.

![freebsd02]

Uncheck (using the spacebar) games, lib32 and ports.

![freebsd03]

Select Auto (UFS)

![freebsd04]

Select Entire Disk

![freebsd05]

Select GPT

![freebsd06]

Select Finish

![freebsd07]

Select Commit

![freebsd08]

Wait for the installation is finished.

![freebsd09]

Set a root password.

![freebsd10]

Select the built-in network interface. And take note of the interface name (if not em0, later changes are required)

![freebsd11]

Select Yes, you want IPv4

![freebsd12]

Select No, you do not want DHCP.

![freebsd13]

Enter the IP manually.

![freebsd14]

Say you *dont* want IPv6 (requires its own / 64)

![freebsd15]

Add two name servers.

![freebsd16]

Select Yes.

![freebsd17]

Select appropriate region.

![freebsd18]

Select appropriate country.

Select Yes.

![freebsd20]

Deselect dumdev (you deselect with spaces).

![freebsd21]

Select **Yes**, you want to add a user (to be able to log on using ssh)

![freebsd22]

Fill in the details, remember to add the new user to the group **wheel**.
Use same password to root.

![freebsd23]
![freebsd24]

Choose exit.

![freebsd25]

Choose No.

![freebsd26]

Eject the virtual CD and select reboot.

![freebsd27]

Wait for the booting of the system.

Configuration in freebsd
------------
We will continue in Terminal, ssh.

```
ssh openvpn@ip
su
# Enter password used for root
freebsd-update fetch install
# Tryck på enter för more sen q för quit.
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
natd_interface="em0" # If ESXi addjust to vmx0 you should have the interface name noted. Use it here.
natd_flags="-dynamic -m"

openvpn_enable="YES"
openvpn_configfile="/usr/local/etc/openvpn/server.conf"
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
```

Add synchronizing the date and time in the crontab.

```
ntpdate ntp1.sth.netnod.se
nano /etc/crontab
00      *       *       *       *       root    ntpdate -s ntp1.sth.netnod.se
```

configure OpenVPN

Adjust push and or server directives
```
nano /usr/local/etc/openvpn/server.conf
```


And finally, reboot the entire machine.

```
reboot
```

Firewall
------------

To OpenVPN to work you need to map the external IP to internally **UDP port 1194**

Examples for Halon securityrouter.org:

```
pass in quick on wan proto udp to wan port 1194 rdr-to 192.168.0.x label OpenVPN
```


If you want to start virtualbox headless.
------------

Instruction needs to be improved.

```
/usr/local/bin/VBoxManage list vms
/usr/local/bin/VBoxManage registervm path to something
```

LaunchDaemon file for automatic start headless:

```
sudo nano /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
sudo chmod 644 /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
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



[freebsd00]: images/freebsd00.png "FreeBSD Install screenshot"
[freebsd01]: images/freebsd01.png "FreeBSD Install screenshot"
[freebsd02]: images/freebsd02.png "FreeBSD Install screenshot"
[freebsd03]: images/freebsd03.png "FreeBSD Install screenshot"
[freebsd04]: images/freebsd04.png "FreeBSD Install screenshot"
[freebsd05]: images/freebsd05.png "FreeBSD Install screenshot"
[freebsd06]: images/freebsd06.png "FreeBSD Install screenshot"
[freebsd07]: images/freebsd07.png "FreeBSD Install screenshot"
[freebsd08]: images/freebsd08.png "FreeBSD Install screenshot"
[freebsd09]: images/freebsd09.png "FreeBSD Install screenshot"
[freebsd10]: images/freebsd10.png "FreeBSD Install screenshot"
[freebsd11]: images/freebsd11.png "FreeBSD Install screenshot"
[freebsd12]: images/freebsd12.png "FreeBSD Install screenshot"
[freebsd13]: images/freebsd13.png "FreeBSD Install screenshot"
[freebsd14]: images/freebsd14.png "FreeBSD Install screenshot"
[freebsd15]: images/freebsd15.png "FreeBSD Install screenshot"
[freebsd16]: images/freebsd16.png "FreeBSD Install screenshot"
[freebsd17]: images/freebsd17.png "FreeBSD Install screenshot"
[freebsd18]: images/freebsd18.png "FreeBSD Install screenshot"
[freebsd19]: images/freebsd19.png "FreeBSD Install screenshot"
[freebsd20]: images/freebsd20.png "FreeBSD Install screenshot"
[freebsd21]: images/freebsd21.png "FreeBSD Install screenshot"
[freebsd22]: images/freebsd22.png "FreeBSD Install screenshot"
[freebsd23]: images/freebsd23.png "FreeBSD Install screenshot"
[freebsd24]: images/freebsd24.png "FreeBSD Install screenshot"
[freebsd25]: images/freebsd25.png "FreeBSD Install screenshot"
[freebsd26]: images/freebsd26.png "FreeBSD Install screenshot"
[freebsd27]: images/freebsd27.png "FreeBSD Install screenshot"
