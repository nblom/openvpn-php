<?php

define(VPN_SERVER,gethostname());
define(VPN_PORT,1194);
define(VPN_KEY, 'openvpn-server.key');
define(VPN_CRT, 'openvpn-server.crt');

define(CAF_KEY, 'ca.key');
define(CAF_CRT, 'ca.crt');
@define(CA_KEY, file_get_contents(CAF_KEY));
@define(CA_CRT, file_get_contents(CAF_CRT));

define(SSL_VALID, (365*2));

$key_config = array(
	"digest_alg" => "sha512",
	"private_key_bits" => 4096,
	"private_key_type" => OPENSSL_KEYTYPE_RSA,
);

date_default_timezone_set('Europe/Stockholm');

# Swedish
#putenv('LC_ALL=sv_SE');
#setlocale(LC_ALL, 'sv_SE');

# English
putenv('LC_ALL=en_US');
setlocale(LC_ALL, 'en_US');
