<?php

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

$users = [ ['itatartrate@precompounding.co.uk'  , 'OCUOpvme'        , 'Susanna'  , 'Whtie'     ]
		 , ['tephramancy@gatewise.co.uk'        , 'bPTFZUWbkQlcJJc' , 'Augustus' , 'Stilwagen' ]
		 , ['unsanctimoniousness@coenoecic.edu' , 'yNKFJTLLpJyP'    , 'Jesenia'  , 'Scanio'    ]
		 , ['extranatural@possumwood.net'       , 'bDGSaFHxAwA'     , 'Geoffrey' , 'Orlander'  ]
		 , ['ureteral@amorphy.edu'              , 'AahymJIyJWLTuA'  , 'Irvin'    , 'Lizaola'   ]
		 ];

$sql = "INSERT INTO `phpant`.`users`
		(`users_email`,
		`users_password`,
		`users_first`,
		`users_last`,
		`users_roles_id`)
		VALUES
		( ?
		, ?
		, ?
		, ?
		, ?
		)";

$pdo->beginTransaction();
$stmt = $pdo->prepare($sql);

foreach($users as $user) {
	$vars = [$user[0], password_hash($user[1], PASSWORD_DEFAULT), $user[2], $user[3], 1];
	$stmt->execute($vars);
}
$pdo->commit();