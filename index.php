<?php
##
#	openvpn-php 
#	Authors: Niklas Blomdalen and Johan Löwenmo, lopnet.se
#	Feel free to modify but please share your improvements.
##

# Check for session function
if (!in_array('openssl', get_loaded_extensions())) die('Missing openssl');
if (!in_array('session', get_loaded_extensions())) die('Missing session');
if (!in_array('gettext', get_loaded_extensions())) die('Missing gettext');


# Start session
session_start();
# If you provide a password, regardless of if it works or not, we store it in our session, unencrypted, yay!?
if (isset($_POST['password'])) $_SESSION['password'] = $_POST['password'];
# If you want to logout, we destroy the session password.
if (isset($_REQUEST['signout'])) unset($_SESSION['password']);
# We are not going to manipulate anything regarding sessions after this line.
session_write_close();

# We need our config.php file, so look in that one for configurable options.
require('config.php');

# If you request a download, and we issued such a certificate, we will give it to you.
if (is_file('issued/'.$_GET['download'])) {
	header('Content-type: application/x-openvpn-profile');
	header('Content-Disposition: attachment; filename="'.gethostname().'-'.basename($_GET['download'],'.crt').'.ovpn"');
	# Our config template gets included.
	include('template.ovpn');
	exit;
}

# Enable local translation of this
bindtextdomain("messages", "./locale");
textdomain("messages");
bind_textdomain_codeset("messages", 'UTF-8');


# Bootstrap gui for non curl browsers, try to make it look good.
if (substr($_SERVER['HTTP_USER_AGENT'],0,4) != 'curl') {
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <title>'._('OpenVPN Administration').'</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin, .form-inline {
        max-width: 800px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }
	.form-inline label {
		padding: 7px 9px;
	}
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <script src="js/jquery.js"></script>
  	<script src="js/bootstrap.min.js"></script>
</head>
<body>';
}

# Kickstart if we are missing CA key and CA certificate.
if ((!is_file(CAF_KEY)) or (!is_file(CAF_CRT))) {
	# Begin automatic CA generation.
	# We randomize a very long string as private key for the certificate.
	$myphrase=generate_password(rand(101,140));
	$cakey = openssl_pkey_new($key_config);
	openssl_pkey_export($cakey, $privKeyStr, $myphrase);
	# The name of the certificate will be set to "hostname" of the machine.
	$dn = array("countryName" => 'SE',"stateOrProvinceName" => 'NA',"localityName" => gethostname(),"organizationName" => gethostname(),"organizationalUnitName" => gethostname(),"commonName" => gethostname());
	if (false===($fn=tempnam("/tmp", "CNF_"))){
		throw new Exception(_('Failed to create temp file').'!');
	}
	# Here we could use some help, whats really needed. Does not have time for trial and error.
	file_put_contents($fn,'HOME = .'."\n".
		'RANDFILE=$ENV::HOME/.rnd'."\n".
		'[req]'."\n".
		'distinguished_name=req_distinguished_name'."\n".
		'[req_distinguished_name]'."\n".
		'[v3_req]'."\n".
		'[v3_ca]'."\n".
		'subjectKeyIdentifier=hash'."\n".
		'authorityKeyIdentifier=keyid:always, issuer'."\n".
		'basicConstraints = CA:true'."\n".
		'keyUsage = keyCertSign, cRLSign'."\n".
		'[CA_default]'."\n");
	$csr = openssl_csr_new($dn, $cakey);
	$cacert = openssl_csr_sign($csr, null, $cakey, (365*20), array('config'=>$fn,"x509_extensions" => "v3_ca",'digest_alg'=>'sha512'), 1451040);
	openssl_pkey_export_to_file($cakey, CAF_KEY, $myphrase);
	openssl_x509_export_to_file($cacert, CAF_CRT);
	# End ca - we now have our CA cert and key stored both in memory and on disk.
	
	# Begin openvpn-server certificate and key generating.
	$privkey = openssl_pkey_new($key_config);
	file_put_contents($fn,'HOME = .'."\n".
		'RANDFILE=$ENV::HOME/.rnd'."\n".
		'[req]'."\n".
		'distinguished_name=req_distinguished_name'."\n".
		'[req_distinguished_name]'."\n".
		'[v3_req]'."\n".
		'[v3_ca]'."\n".
		'subjectKeyIdentifier=hash'."\n".
		'basicConstraints = CA:false'."\n".
		'keyUsage = digitalSignature, keyEncipherment'."\n".
		'extendedKeyUsage = serverAuth'."\n".
		'[CA_default]'."\n");
	$csr = openssl_csr_new($dn, $privkey);
	$sscert = openssl_csr_sign($csr, $cacert, $cakey, (365*20),  array('config'=>$fn,"x509_extensions" => "v3_ca",'digest_alg'=>'sha512'), 1451050);
	# Save openvpn-server certificate signed with our CA certificate.
	openssl_pkey_export_to_file($privkey, VPN_KEY); # without password for openvpn, could not be used as clientAuth anyway.
	openssl_x509_export_to_file($sscert, VPN_CRT);
	
	# Lets output the passphrase we generated, your one and only chanse to keep it!
	# If curl, no fancy schmancy output. 
	if (substr($_SERVER['HTTP_USER_AGENT'],0,4) == 'curl') {
		die ("\n\n\t"._('Password').': '.$myphrase."\n\n");
	}
	# Otherwise a bit fancy.
	$message='<br />'."\n"._('CA cert where missing, generated fresh for you with password').': <pre>'."\n".$myphrase."\n".'</pre>'."\n";
	die ($message);
}

# If you are trying to use curl again to kickstart, give nice error message that you are screwed.
if (substr($_SERVER['HTTP_USER_AGENT'],0,4) == "curl") {
	echo _('Curl is not supported here').".\n";
	echo _('If you forgot your password you need to delete the').' ca.* '._('and').' openvpn-server.* '._('files').".\n";
	echo _('And start all over again').".\n";
	die();
}

# Try and open ca private key with user provided password.
$cakey=openssl_pkey_get_private(CA_KEY,$_SESSION['password']);
if ($cakey === false) {
	# In case of brute force, sleep 1 second. Surely you should protect your plan text http server better. Ie, only local access.
	# Oh, captcha would be awesome. Perhaps some day.
	sleep(1);
	echo '<form class="form-signin" method="post" action="./">
        <h2 class="form-signin-heading">'._('OpenVPN Administration').'</h2>
         <label for="password">'._('Password').': </label><input type="password" id="password" name="password" class="input-block-level" placeholder="'._('Supersecret password').'">
        <button class="btn btn-large btn-primary" type="submit">'._('Login').'</button></form>';
    # If you failed to unlock the password
    if (isset($_POST['password'])) {
    	echo '<div class="form-signin">';
		echo '<p class="bg-warning">'._('You are most likely trying to use a faulty password.').'</p>';
		echo '</div>';
    }
    echo '</body></html>';
	exit; # we are not using die here since we dont want the server give any hints upon successfull or not.
}

# Ok, successful unlock of private key, lets offer new certificate signage.
echo '<form class="form-inline" method="post" action="./">';
echo '<h2 class="form-signin-heading">'._('OpenVPN Configurations').'</h2>';
echo '<label for="name">'._('Name').': </label><input class="form-control" type="text" id="name" name="name" maxlength="25" placeholder="'._('john.doe').'" data-toggle="tooltip" title="'._('Lowercase latin letters').'" />';
echo '<label for="phrase">'._('Password').': </label><input class="form-control" type="text" id="phrase" name="phrase" value="'.generate_password(rand(40,60)).'" size="61" data-toggle="tooltip" title="'._('Does not get saved').'!" />';
echo '<label for="submit"></label><input type="submit" id="submit" value="'._('Create').'" class="btn btn-default" />';
echo '</form>';

# Creating base dirs.
if (!is_dir('issued/revoked')) mkdir('issued/revoked',0755,true);
# Creating empty database
if (!is_file('index.txt')) touch('index.txt');


# If name is set, and longer than 2 characters, try and create a new cert.
if (strlen($_POST['name']) > 2) {
	# Lets remove vierd charcters like spaces.
	$_POST['name'] = strtolower(str_replace(' ','.',trim($_POST['name'])));
	# Importing number of issued including revoked certifikates to calculate next serial.
	$serial = ((count(scandir('issued'))+count(scandir('issued/revoked')))/3);
	# Checking for non latin characters
	if ($_POST['name'] != htmlentities($_POST['name'])) {
		echo '<div class="form-signin ">';
		echo '<p class="bg-warning">'._('Your certificate contains non latin characters').'.</p>';
		echo '</div>';
	}
	else if (is_file('issued/'.$_POST['name'].'.csr')) {
		echo '<div class="form-signin ">';
		echo '<p class="bg-warning">'._('You need a globally unique name on your certificates').'. ('._('Revoked/deleted certificates still count').')</p>';
		echo '</div>';
	}
	else {
		$dn = array("commonName" => $_POST['name']);
		if (false===($fn=tempnam("/tmp", "CNF_"))){
			throw new Exception(_('Failed to create temp file').'!');
		}
		file_put_contents($fn,
			'HOME = .'."\n".
			'RANDFILE=$ENV::HOME/.rnd'."\n".
			'[req]'."\n".
			'distinguished_name=req_distinguished_name'."\n".
			'[req_distinguished_name]'."\n".
			'[v3_req]'."\n".
			'[v3_ca]'."\n".
			'[CA_default]'."\n".
			"\n"
		);
		
		$privkey = openssl_pkey_new($key_config);
		$csr = openssl_csr_new($dn, $privkey, array('config'=>$fn,"req_extensions" => "v3_req",'digest_alg'=>'sha512'));
			
		$sscert = openssl_csr_sign($csr, CA_CRT, $cakey, SSL_VALID, array('config'=>$fn,"x509_extensions" => "v3_ca",'digest_alg'=>'sha512'), $serial);
		openssl_pkey_export_to_file($privkey, 'issued/'.$_POST['name'].'.key', $_POST['phrase']);
		openssl_x509_export_to_file($sscert, 'issued/'.$_POST['name'].'.crt');
		openssl_csr_export_to_file($csr,'issued/'.$_POST['name'].'.csr');
		echo '<div class="form-signin">';
		echo '<h3 class="form-signin-heading">'._('Password for').' '.$_POST['name'].':</h3><samp>'.$_POST['phrase'].'</samp><h6>'._('Passwords are not saved, they are only showed here once').'.</h6>';
		echo '<a href="?download='.$_POST['name'].'.crt" class="btn btn-primary btn-xs" role="button">'._('Fetch').'</a>';
		echo '</div>';
		exec('openssl ca -config openssl.cnf -key "'.$_SESSION['password'].'" -valid issued/'.$_POST['name'].'.crt',$return,$exit);
		exec('openssl ca -config openssl.cnf -key "'.$_SESSION['password'].'" -gencrl -out intermediate.crl.pem');
	}
}

# If revoke is set, check if file excist and revoke cert and update crl.
if (is_file('issued/'.$_GET['revoke'])) {
	exec('openssl ca -config openssl.cnf -key "'.$_SESSION['password'].'" -revoke issued/'.$_GET['revoke'],$return,$exit);
	if ($exit != 0) {
		echo '<div class="form-signin ">';
		echo '<p class="bg-warning">'._('Revokation failed').', '.implode("<br />\n",$return).'</p>';
		echo '</div>';
	}
	exec('openssl ca -config openssl.cnf -key "'.$_SESSION['password'].'" -gencrl -out intermediate.crl.pem');
}

# if remove is set, move files to revoked folder (to keep track of serial) and rescan issued dir.
if (is_file('issued/'.$_GET['remove'])) {
	@rename('issued/'.$_GET['remove'],'issued/revoked/'.$_GET['remove']);
	@rename('issued/'.basename($_GET['remove'],'.crt').'.key','issued/revoked/'.basename($_GET['remove'],'.crt').'.key');
	@rename('issued/'.basename($_GET['remove'],'.crt').'.csr','issued/revoked/'.basename($_GET['remove'],'.crt').'.csr');
}

# Reading issued certificates
$issued=scandir('issued');
if (count($issued) > 0) {
	# Sort out those who are revoked:
	$database = explode("\n",file_get_contents('index.txt'));
	foreach ($database as $row) {
		$revo = explode("\t",$row);
		$p=base64_encode($revo[5]);
		$revoked[$p] = $revo[0];
	}
	unset($database);
	
	# Start outputing table for all certificates in issued.
	echo '<div class="table-responsive">';
	echo '<div class="form-signin">';
	echo '<table width="100%" class="table table-striped">';
	echo '<tr>';
	echo '<th>'._('Status').'</th>';
	echo '<th>'._('Name').'</th>';
	echo '<th>'._('Serialnumber').'</th>';
	echo '<th>'._('Valid from').'</th>';
	echo '<th>'._('Valid to').'</th>';
	echo '<th colspan="2"></th>';
	echo "</tr>";
	echo "\n";
	foreach ($issued AS $issue) {
		if (substr($issue,-4) == '.crt') {
			# Validate (syntax) of certificate and show if valid. Otherwise some junk data.
			$data = openssl_x509_parse(file_get_contents('issued/'.$issue));
			if ($data !== false) {
				echo '<tr>';
				echo '<td>';
				$p=base64_encode($data['name']);
				if ($revoked[$p] == 'R') {
					echo '<span class="label label-important">'._('Revoked').'</span>';
				}
				else if ( $data['validTo_time_t'] < (time()+(30*24*3600))) {
					echo '<span class="label label-warning">'._('Best before').'</span>';
				}
				else {
					echo '<span class="label label-success">'._('Active').'</span>';
				}
				echo '</td>';
				echo '<td>';
				echo $data['subject']['CN'];
				echo '</td>';
				echo '<td>';
				echo $data['serialNumber'];
				echo '</td>';
				echo '<td>';
				echo date('Y-m-d', $data['validFrom_time_t']);
				echo '</td>';
				echo '<td>';
				echo date('Y-m-d', $data['validTo_time_t']);
				echo '</td>';
				echo '<td>';
				if ($revoked[$p] != 'R') {
					echo '<a href="?revoke='.$issue.'" class="btn btn-danger btn-xs" role="button">'._('Revoke').'</a>';
				}
				if ($revoked[$p] == 'R') {
					echo '<a href="?remove='.$issue.'" class="btn btn-warning btn-xs" role="button">'._('Delete').'</a>';
				}
				echo '</td>';
				echo '<td>';
				if ($revoked[$p] != 'R') {
					echo '<a href="?download='.$issue.'" class="btn btn-primary btn-xs" role="button">'._('Fetch').'</a>';
				}
				echo '</td>';
				echo '</tr>';
				echo "\n";
			}
			else {
				echo _('Something went wrong when parsing certficate').' "'.$issue.'"';
			}
		}
	}
	echo '<tr><td colspan="6"></td><td>';
	echo '<a class="btn btn-info" href="?signout=true">'._('Log out').'</a>';
	echo '</td></tr>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
else {
	die (_('Something went wrong when reading directory').' "issued"');
}

echo "<script>
$( document ).ready(function() {
	$('[data-toggle=\"tooltip\"]').tooltip({'placement': 'bottom'});
});
</script>";
echo '</body></html>';

# Small function to generate pretty random passwords that can be double clicked (not containing -& and åäö characters)
function generate_password($length=48) {
	$validchars='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$arr = str_split($validchars.$validchars.$validchars.$validchars.$validchars);
	shuffle($arr);
	$arr = array_slice($arr, 0, $length);
	return implode('', $arr);
}

?>