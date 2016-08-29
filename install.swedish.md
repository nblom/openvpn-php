Virtualbox
------------

Installera Virtualbox och skapa en ny FreeBSD 64 maskin enligt:

Hämta [ftp://ftp.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso](ftp://ftp.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso)


* 1 CPU
* 1 gb ram
* 10 gb hdd
* Byt från NAT nätverk till Bridge.
* Montera CD med iso fil.
* Starta den virtuella maskinen.

Installation
------------
När du startar upp FreeBSD från cd, välj följande:

![freebsd00]

saknar skärmdump på alternativet swedish iso

![freebsd01]

Välj ett hostname som du kommer peka i dns. **Glöm inte peka hosname mot externt IP i dnsen**, gör det redan nu för att vara säker.

![freebsd02]

Kryssa ur (med hjälp av mellanslag) games, lib32 och ports.

![freebsd03]

Välj Auto (UFS)

![freebsd04]

Välj Entire Disk

![freebsd05]

Välj GPT

![freebsd06]

Välj Finish

![freebsd07]

Välj Commit

![freebsd08]

Vänta på att installationen går färdigt.

![freebsd09]

Sätt ett root lösenord.

![freebsd10]

Välj det inbyggda nätverksinterfacet.

![freebsd11]

Välj Ja, du vill ha IPv4

![freebsd12]

Välj Nej, du vill inte ha DHCP.

![freebsd13]

Lägg in ett IP manuellt.

![freebsd14]

Säg att du *INTE* vill ha IPv6 (kräver eget /64)

![freebsd15]

Lägg till två namnservar.

![freebsd16]

Välj Nej.

![freebsd17]

Välj Europa.

![freebsd18]

Välj Sweden.

Välj Ja.

![freebsd20]

Välj bort dumdev (du avmarkerar med mellanslag).

![freebsd21]

Välj **Ja**, du vill lägga till en användare (annars kan du inte logga in på ssh)

![freebsd22]

Fyll i uppgifterna, tänk på att lägga till den nya användaren i gruppen **wheel**.
Sätt lämpligvis samma lösenord som till root.

![freebsd23]
![freebsd24]

Välj exit.

![freebsd25]

Välj No.

![freebsd26]

Mata ut den virtuella CD:n och välj reboot.

![freebsd27]

Vänta på omstart av systemet.

Konfiguration i freebsd
------------
Vi fortsätter i Terminal, med ssh.

```
ssh openvpn@ip
su
freebsd-update fetch install
# Tryck på enter för more sen q för quit.
pkg install -y nano screen bash git openvpn apache24 php56-openssl php56-session php56-gettext mod_php56
bash
```

Autostart i freebsd

```
nano /etc/rc.conf
```

Lägg till följande längst ner:

```
apache24_enable="YES"
firewall_enable="YES"
firewall_type="open"

gateway_enable="YES"
natd_enable="YES"
natd_interface="em0" # Om ESXi justera till vmx0
natd_flags="-dynamic -m"

openvpn_enable="YES"
openvpn_configfile="/usr/local/etc/openvpn/server.conf"
```

## Hämta git repo
```
rm -fr /usr/local/www/apache24/data/
git clone https://github.com/nblom/openvpn-php.git /usr/local/www/apache24/data/
chmod 777 /usr/local/www/apache24/data/
```

## Konfigurera apache
```
cp /usr/local/www/apache24/data/apache.conf /usr/local/etc/apache24/Includes/apache.conf
service apache24 start
```

## Konfigurera openvpn
```
mkdir /usr/local/etc/openvpn/
cp /usr/local/www/apache24/data/server.conf /usr/local/etc/openvpn/
openssl dhparam -out /usr/local/etc/openvpn/dh.pem 2048
```

Anropa index.php via curl (så skapas rätt filer med rätt behörighet)

```
curl localhost
```
### Spara lösenordet som genererats!

Flytta sen openvpn filerna och kopiera ca certfikatet.

```
mv /usr/local/www/apache24/data/openvpn-server.* /usr/local/etc/openvpn/
cp /usr/local/www/apache24/data/ca.crt /usr/local/etc/openvpn/
chmod 600 /usr/local/etc/openvpn/openvpn-server.key 
```

Lägg till synkronisering av datum och tid i crontab.

```
ntpdate ntp1.sth.netnod.se
nano /etc/crontab
00      *       *       *       *       root    ntpdate -s ntp1.sth.netnod.se
```

Konfigurera OpenVPN

Justera push "route 192.168.x.0 255.255.255.0"
```
nano /usr/local/etc/openvpn/server.conf
```


Och slutligen, starta om hela maskinen.

```
reboot
```

Brandvägg
------------

För att openvpn ska fungera behöver du mappa externt ip till internt för **UDP port 1194**

Exempel i Halon:

```
pass in quick on wan proto udp to wan port 1194 rdr-to 192.168.0.x label OpenVPN
```


Om du vill starta virtualbox headless.
------------

Instruktion behöver kompletteras.

```
/usr/local/bin/VBoxManage list vms
/usr/local/bin/VBoxManage registervm path till openvpn.något
```

LaunchDaemon fil för automatiskt start headless:

```
sudo nano /Library/LaunchDaemons/se.lop.virtualbox.openvpn.plist
```

Lägg in namn om du inte döpte maskinen till "OpenVPN"

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
