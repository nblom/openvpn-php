# openvpn-php webgui for openvpn ca
> Authors: Niklas Blomdalen, Johan Löwenmo, lopnet.se

Login

![Login screnshot](images/openvpn-php1.png?raw=true "Login" =450x)

Created user joe.banana

![User screnshot](images/openvpn-php2.png?raw=true "Created new user" =450x)

List of all users including revoked ones.

![List screnshot](images/openvpn-php3.png?raw=true "List of users" =450x)


Installation
------------
See [install.md](install.md)

Configuration
------------

Very little configuration is needed.

* In server.conf (/usr/local/etc/openvpn/server.conf)
* * You most likely want to change push routes.
* * You might want to change ip range "server" to something unique.
* * You might want to change default gateway for clients and push some dns servers if you want all traffic to go through the VPN.
* In config.php (/usr/local/www/apache24/data/config.php)
* * You might want to adjust the SSL_VALID time, its set to 2 years now.
* * You might want to set a specific hostname for client configurations, VPN_SERVER, but its automatically the vms hostname.
* * You can change language to swedish (for now) for the webgui.
* * You can set the strength used for new keys by changing the private_key_bits to 2048 or whatever (default is 4096)
