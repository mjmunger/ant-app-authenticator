<?php

//Seed authentication tokens for all the users.

function generateToken() {
	if(version_compare(phpversion(), '7.0.0','<')) {
		$seed = bin2hex(openssl_random_pseudo_bytes(16));
	} else {
		$seed = bin2hex(random_bytes(16));		
	}
	$token = hash_hmac('sha256',$seed, 'Eevohnie0oN2aht');
	return $token;
}

$deps = [ '../../../mysql-credentials.php'
		];

foreach($deps as $d) {
	include($d);
}

$dsn = "mysql:dbname=$dbDatabase;host=$dbHost";
try {
    $pdo = new PDO($dsn, $dbUsername, $dbPassword);
} catch (PDOException $e) {
    print PHP_EOL;
    print str_pad('', 80,'*') . PHP_EOL;
    printf('gimmiePDODB failure: %s' . PHP_EOL,$e->getMessage());
    print str_pad('', 80,'*') . PHP_EOL;
    print PHP_EOL;
}

$sql = "INSERT INTO `phpant`.`user_tokens` (`user_tokens_token`, `user_tokens_expiry`, `users_id`) VALUES (?, ?, ?)";

$pdo->beginTransaction();
$stmt = $pdo->prepare($sql);

//Do 6 session cookies.
for($x = 1; $x <=6; $x++) {
	$vars = [ generateToken()
	        , date("Y-m-d H:i:s", time() + 3600 * 24)
	        , $x
	        ];
	$stmt->execute($vars);
}

//six more that expire in 10 days
for($x = 1; $x <=6; $x++) {
	$vars = [ generateToken()
	        , date("Y-m-d H:i:s", time() + 3600 * 24 * 10)
	        , $x
	        ];
	$stmt->execute($vars);
}

//six more that expire in 30 days.
for($x = 1; $x <=6; $x++) {
	$vars = [ generateToken()
	        , date("Y-m-d H:i:s", time() + 3600 * 24 * 30)
	        , $x
	        ];
	$stmt->execute($vars);
}

$pdo->commit();
