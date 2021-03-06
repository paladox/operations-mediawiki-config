<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# etcd.php provides wmfSetupEtcd() which CommonSettings.php usees
# to load certain configuration variables from Etcd.
#
# This for PRODUCTION.
#
# This is loaded very early. Only one set of globals should be
# assumed here:
# - $wmfRealm, $wmfDatacenter (from multiversion/MWRealm)
#
# Effective load order:
# - multiversion
# - mediawiki/DefaultSettings.php
# - wmf-config/etcd.php [THIS FILE]
#
# Included from: wmf-config/CommonSettings.php.
#

/**
 * Setup etcd!
 *
 * @param string|null $etcdHost Hostname
 * @return EtcdConfig
 */
function wmfSetupEtcd( $etcdHost ) {
	# Create a local cache
	if ( PHP_SAPI === 'cli' || !function_exists( 'apcu_fetch' ) ) {
		$localCache = new HashBagOStuff;
	} else {
		$localCache = new APCUBagOStuff;
	}

	# Use a single EtcdConfig object for both local and common paths
	$etcdConfig = new EtcdConfig( [
		'host' => $etcdHost,
		'protocol' => 'https',
		'directory' => "conftool/v1/mediawiki-config",
		'cache' => $localCache,
	] );
	return $etcdConfig;
}

/** In production, read the database loadbalancer config from etcd.
 * See https://wikitech.wikimedia.org/wiki/Dbctl
 *
 * This function must be called after db-{eqiad,codfw}.php has been loaded!
 * It overwrites a few sections of $wgLBFactoryConf with data from etcd.
 */
function wmfEtcdApplyDBConfig() {
	global $wgLBFactoryConf, $wmfDbconfigFromEtcd, $wmfRealm;
	// In labs, the relevant key exists in etcd, but does not contain real data.
	// Only do this in production.
	if ( $wmfRealm === 'production' ) {
		$wgLBFactoryConf['readOnlyBySection'] = $wmfDbconfigFromEtcd['readOnlyBySection'];
		$wgLBFactoryConf['groupLoadsBySection'] = $wmfDbconfigFromEtcd['groupLoadsBySection'];
		$wgLBFactoryConf['hostsByName'] = $wmfDbconfigFromEtcd['hostsByName'];

		// Because JSON dictionaries are unordered, but the order of sectionLoads & externalLoads is
		// rather significant to Mediawiki, dbctl stores the master in a dictionary by itself,
		// and then the remaining replicas in a second dict.
		foreach ( $wmfDbconfigFromEtcd['sectionLoads'] as $section => $sectionLoads ) {
			$wgLBFactoryConf['sectionLoads'][$section] = array_merge( $sectionLoads[0], $sectionLoads[1] );
		}
		// Mediawiki has many different names for the same external storage clusters in dbctl.
		// This translates from dbctl's name for a cluster to a set of Mediawiki names for it.
		$externalStoreNameMap = [
			# es1, previously known as $wmgOldExtTemplate
			'es1' => [ 'rc1', 'cluster3', 'cluster4', 'cluster5', 'cluster6', 'cluster7',
					   'cluster8', 'cluster9', 'cluster10', 'cluster20', 'cluster21',
					   'cluster1', 'cluster2', 'cluster22', 'cluster23' ],
			'es2' => [ 'cluster24' ],
			'es3' => [ 'cluster25' ],
			'es4' => [ 'cluster26' ],
			'es5' => [ 'cluster27' ],
			'x1' => [ 'extension1' ],
		];
		foreach ( $wmfDbconfigFromEtcd['externalLoads'] as $dbctlName => $dbctlLoads ) {
			$merged = array_merge( $dbctlLoads[0], $dbctlLoads[1] );
			foreach ( $externalStoreNameMap[$dbctlName] as $mwLoadName ) {
				$wgLBFactoryConf['externalLoads'][$mwLoadName] = $merged;
			}
		}
	}
}
