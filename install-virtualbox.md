Virtualbox
------------

Installing VirtualBox

* Download VirtualBox from [virtualbox.org](https://www.virtualbox.org/wiki/Downloads)

Install the app, you know, continue, continue, agree, continue, install etc.

![Virtualbox screenshot](images/virtualbox01.png?raw=true)

![Virtualbox screenshot](images/virtualbox02.png?raw=true)

![Virtualbox screenshot](images/virtualbox03.png?raw=true)

![Virtualbox screenshot](images/virtualbox04.png?raw=true)

Launch VirtualBox when installed.

![Virtualbox screenshot](images/virtualbox05.png?raw=true)

Create a new Virtual machine preferably with name "OpenVPN" and Type BSD, version FreeBSD (64-bit)

![Virtualbox screenshot](images/virtualbox06.png?raw=true)

A memory size of 1024 should be enough.

![Virtualbox screenshot](images/virtualbox07.png?raw=true)

I guess, create a new virtual hard disk now.

![Virtualbox screenshot](images/virtualbox08.png?raw=true)

The format, oh, yeah, eeeh.. VDI?

![Virtualbox screenshot](images/virtualbox09.png?raw=true)

Dynamically seems much better. Im all for saving storage.

![Virtualbox screenshot](images/virtualbox10.png?raw=true)

Size, well fully installed it will take about 1.4 gigs. So, lets go crazy and give it 10 GB of storage.

![Virtualbox screenshot](images/virtualbox11.png?raw=true)

Then select Settings.

![Virtualbox screenshot](images/virtualbox12.png?raw=true)

Under Storage, on the Empty optical drive.

![Virtualbox screenshot](images/virtualbox15.png?raw=true)

Choose Virtual Optical Disk file on the CD icon, and select the FreeBSD ISO that you downloaded from [ftp://ftp2.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso](ftp://ftp2.se.freebsd.org/pub/FreeBSD/releases/ISO-IMAGES/10.3/FreeBSD-10.3-RELEASE-amd64-disc1.iso)

![Virtualbox screenshot](images/virtualbox16.png?raw=true)

![Virtualbox screenshot](images/virtualbox17.png?raw=true)

Then select Network and change Attached to: Bridged Adapter instead.

![Virtualbox screenshot](images/virtualbox18.png?raw=true)

Start the virtual machine.

[Next step - installing freebsd in the Virtualbox VM](install-freebsd.md)

