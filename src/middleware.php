<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
// configure middleware

$app->add(new \Gofabian\Negotiation\NegotiationMiddleware([
	'accept' => ['text/html', 'application/json']
]));
