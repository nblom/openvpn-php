[Previous step - installing virtualbox](install-virtualbox.md)

Installation
------------
When you boot FreeBSD from the CD, select the following:

![FreeBSD install step](images/freebsd00.png?raw=true)

Choose a keyboard that suits you.

![FreeBSD install step](images/freebsd01.png?raw=true)

Choose a hostname that you will point to the DNS. **Do not forget to point hosname against external IP in dnsen**, do it now, just to be sure.

![FreeBSD install step](images/freebsd02.png?raw=true)

Uncheck (using the spacebar) games, lib32 and ports.

![FreeBSD install step](images/freebsd03.png?raw=true)

Select Auto (UFS)

![FreeBSD install step](images/freebsd04.png?raw=true)

Select Entire Disk

![FreeBSD install step](images/freebsd05.png?raw=true)

Select GPT

![FreeBSD install step](images/freebsd06.png?raw=true)

Select Finish

![FreeBSD install step](images/freebsd07.png?raw=true)

Select Commit

![FreeBSD install step](images/freebsd08.png?raw=true)

Wait for the installation is finished.

![FreeBSD install step](images/freebsd09.png?raw=true)

Set a root password.

![FreeBSD install step](images/freebsd10.png?raw=true)

Select the built-in network interface. And take note of the interface name (if not em0, later changes are required)

![FreeBSD install step](images/freebsd11.png?raw=true)

Select Yes, you want IPv4

![FreeBSD install step](images/freebsd12.png?raw=true)

Select No, you do not want DHCP.

![FreeBSD install step](images/freebsd13.png?raw=true)

Enter the IP manually.

![FreeBSD install step](images/freebsd14.png?raw=true)

Say you *dont* want IPv6 (requires its own / 64)

![FreeBSD install step](images/freebsd15.png?raw=true)

Add two name servers.

![FreeBSD install step](images/freebsd16.png?raw=true)

Select Yes.

![FreeBSD install step](images/freebsd17.png?raw=true)

Select appropriate region.

![FreeBSD install step](images/freebsd18.png?raw=true)

Select appropriate country.

Select Yes.

![FreeBSD install step](images/freebsd20.png?raw=true)

Deselect dumdev (you deselect with spaces).

![FreeBSD install step](images/freebsd21.png?raw=true)

Select **Yes**, you want to add a user (to be able to log on using ssh)

![FreeBSD install step](images/freebsd22.png?raw=true)

Fill in the details, remember to add the new user to the group **wheel**.
Use same password to root.

![FreeBSD install step](images/freebsd23.png?raw=true)
![FreeBSD install step](images/freebsd24.png?raw=true)

Choose exit.

![FreeBSD install step](images/freebsd25.png?raw=true)

Choose No.

![FreeBSD install step](images/freebsd26.png?raw=true)

Eject the virtual CD and select reboot.

![Virtualbox screenshot](images/virtualbox20a.png?raw=true)

![FreeBSD install step](images/freebsd27.png?raw=true)

Wait for booting to be complete.

[Next step - configurating freebsd](install.md)


