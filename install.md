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

![FreeBSD install step](images/freebsd00.png)

Choose a keyboard that suits you.

![FreeBSD install step](images/freebsd01.png)

Choose a hostname that you will point to the DNS. **Do not forget to point hosname against external IP in dnsen**, do it now, just to be sure.

![FreeBSD install step](images/freebsd02.png)

Uncheck (using the spacebar) games, lib32 and ports.

![FreeBSD install step](images/freebsd03.png)

Select Auto (UFS)

![FreeBSD install step](images/freebsd04.png)

Select Entire Disk

![FreeBSD install step](images/freebsd05.png)

Select GPT

![FreeBSD install step](images/freebsd06.png)

Select Finish

![FreeBSD install step](images/freebsd07.png)

Select Commit

![FreeBSD install step](images/freebsd08.png)

Wait for the installation is finished.

![FreeBSD install step](images/freebsd09.png)

Set a root password.

![FreeBSD install step](images/freebsd10.png)

Select the built-in network interface. And take note of the interface name (if not em0, later changes are required)

![FreeBSD install step](images/freebsd11.png)

Select Yes, you want IPv4

![FreeBSD install step](images/freebsd12.png)

Select No, you do not want DHCP.

![FreeBSD install step](images/freebsd13.png)

Enter the IP manually.

![FreeBSD install step](images/freebsd14.png)

Say you *dont* want IPv6 (requires its own / 64)

![FreeBSD install step](images/freebsd15.png)

Add two name servers.

![FreeBSD install step](images/freebsd16.png)

Select Yes.

![FreeBSD install step](images/freebsd17.png)

Select appropriate region.

![FreeBSD install step](images/freebsd18.png)

Select appropriate country.

Select Yes.

![FreeBSD install step](images/freebsd20.png)

Deselect dumdev (you deselect with spaces).

![FreeBSD install step](images/freebsd21.png)

Select **Yes**, you want to add a user (to be able to log on using ssh)

![FreeBSD install step](images/freebsd22.png)

Fill in the details, remember to add the new user to the group **wheel**.
Use same password to root.

![FreeBSD install step](images/freebsd23.png)
![FreeBSD install step](images/freebsd24.png)

Choose exit.

![FreeBSD install step](images/freebsd25.png)

Choose No.

![FreeBSD install step](images/freebsd26.png)

Eject the virtual CD and select reboot.

![FreeBSD install step](images/freebsd27.png)

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

Click Convert Configurations

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

