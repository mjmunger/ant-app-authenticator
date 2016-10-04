<?php

namespace PHPAnt\Authentication;

class RequestFactory {
	
	static function getRequestAuthorization($options) {

		$uri         = $options['uri'];
		$pdo         = $options['pod'];
		$credentials = $options['credentials'];

        /**
         *  Determine if the request is for an API or content. 
         *  All APIs must have /api/ as the root of the URI. You can have many,
         *  many APIs, but they must all be under /api/
         *
         * Example:
         * /api/tickets/v1/view/
         * /api/tickets/v1/view/23
         * /api/guests/v2/register/
         *
         **/

        $regex = '%^\/api\/.*%';
        $Request = (preg_match($regex, $uri) > 0 
                   ? new AuthorizeAPI($pdo, $uri, $credentials)
                   : new AuthorizePageview($pdo, $uri, $credentials)
                   );

        return $Request;
	}
}