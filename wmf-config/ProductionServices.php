<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# ProductionServices.php statically defines all service hostnames/ips
# for any service used by MediaWiki, divided by datacenter.
#
# May be included on app servers in contexts where MediaWiki is not (yet)
# initialised (for example, fatal-error.php and profiler.php).
#
# MUST NOT use any predefined state, only primitive values in plain PHP.
#
# This for PRODUCTION.
#
# For MediaWiki, this is included from ../src/ServiceConfig.php
#
# ATTENTION: do NOT add new services below without asking SRE to set up a
# service proxy for it first. See T244843 for the rationale.
# All proxies that  are already setup can be found within operations/puppet,
# file hieradata/common/profile/services_proxy/envoy.yaml

$common = [
	// Logging is not active-active.
	'udp2log' => 'mwlog1001.eqiad.wmnet:8420',
	'xenon' => 'mwlog1001.eqiad.wmnet',

	// This refers to the old, MongoDB-based backend, which has been
	// replaced by xhgui-pdo (T180761).
	'xhgui' => null,

	// Database username and password for XHGui are set in PrivateSettings.php
	'xhgui-pdo' => 'mysql:host=m2-master.eqiad.wmnet;dbname=xhgui',

	// Statsd is not active-active.
	'statsd' => 'statsd.eqiad.wmnet',

	// EventLogging is not active-active.
	'eventlogging' => 'udp://10.64.32.167:8421', # eventlog1001.eqiad.wmnet

	// Logstash is not active-active.
	'logstash' => [
		'10.2.2.36', # logstash.svc.eqiad.wmnet
	],

	// IRC (broadcast RCFeed for irc.wikimedia.org)
	// Not active-active.
	'irc' => '208.80.153.44', # kraz.codfw.wmnet

	// Automatic dc-local discovery
	'parsoid' => 'http://localhost:6002/w/rest.php',
	'mathoid' => 'http://localhost:6003',
	'eventgate-analytics' => 'http://localhost:6004',
	'eventgate-main' => 'http://localhost:6005',
	'cxserver' => 'http://localhost:6015',
	'electron' => 'http://pdfrender.discovery.wmnet:5252',
	'restbase' => 'http://restbase.discovery.wmnet:7231',
	'sessionstore' => 'http://localhost:6006',
	'echostore' => 'http://localhost:6007',
	'push-notifications' => 'http://localhost:6012',

	// cloudelastic only exists in eqiad. No load balancer is available due to
	// the part of the network that they live in so each host is enumerated

	// WARNING: psi and omega have their ports "mixed up", see https://phabricator.wikimedia.org/T262630
	'cloudelastic-chi' => [
		[ // forwarded to https://cloudelastic.wikimedia.org:9243/
			'host' => 'localhost',
			'transport' => 'Http',
			'port' => 6105,
		],
	],
	'cloudelastic-psi' => [
		[ // forwarded to https://cloudelastic.wikimedia.org:9443/
			'host' => 'localhost',
			'transport' => 'Http',
			'port' => 6107,
		],
	],
	'cloudelastic-omega' => [
		[ // forwarded to https://cloudelastic.wikimedia.org:9643/
			'host' => 'localhost',
			'transport' => 'Http',
			'port' => 6106,
		],
	]
];

$services = [
	'eqiad' => $common + [
		// each DC has its own urldownloader for latency reasons
		'urldownloader' => 'http://url-downloader.eqiad.wikimedia.org:8080',

		'upload' => 'ms-fe.svc.eqiad.wmnet',
		'mediaSwiftAuth' => 'https://ms-fe.svc.eqiad.wmnet/auth',
		'mediaSwiftStore' => 'https://ms-fe.svc.eqiad.wmnet/v1/AUTH_mw',

		'etcd' => '_etcd._tcp.eqiad.wmnet',

		'poolcounter' => [
			'10.64.0.151', # poolcounter1004.eqiad.wmnet
			'10.64.32.236', # poolcounter1005.eqiad.wmnet
		],

		// LockManager Redis
		'redis_lock' => [
			'rdb1' => '10.64.0.80', # mc1019
			'rdb2' => '10.64.16.107', # mc1024
			'rdb3' => '10.64.48.155', # mc1033
		],
		'search-chi' => [
			[ // forwarded to https://search.svc.eqiad.wmnet:9243/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6102,
			]
		],
		'search-psi' => [
			[ // forwarded to https://search.svc.eqiad.wmnet:9643/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6104,
			]
		],
		'search-omega' => [
			[ // forwarded to https://search.svc.eqiad.wmnet:9443/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6103,
			]
		],
	],
	'codfw' => $common + [
		'urldownloader' => 'http://url-downloader.codfw.wikimedia.org:8080',

		'upload' => 'ms-fe.svc.codfw.wmnet',
		'mediaSwiftAuth' => 'https://ms-fe.svc.codfw.wmnet/auth',
		'mediaSwiftStore' => 'https://ms-fe.svc.codfw.wmnet/v1/AUTH_mw',

		'etcd' => '_etcd._tcp.codfw.wmnet',

		'poolcounter' => [
			'10.192.0.132', # poolcounter2003.codfw.wmnet
			'10.192.16.129', # poolcounter2004.codfw.wmnet
		],

		'redis_lock' => [
			'rdb1' => '10.192.0.83', # mc2019
			'rdb2' => '10.192.0.84', # mc2020
			'rdb3' => '10.192.0.85', # mc2021
		],
		'search-chi' => [
			[ // forwarded to https://search.svc.codfw.wmnet:9243/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6202,
			]
		],
		'search-psi' => [
			[ // forwarded to https://search.svc.codfw.wmnet:9643/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6204,
			]
		],
		'search-omega' => [
			[ // forwarded to https://search.svc.codfw.wmnet:9443/
				'host' => 'localhost',
				'transport' => 'Http',
				'port' => 6203,
			]
		],
	],
];
unset( $common );
return $services;
