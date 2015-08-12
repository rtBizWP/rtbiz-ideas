#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=/tmp/wordpress/

set -ex

install_wp() {
    rm -rf $WP_CORE_DIR
	mkdir -p $WP_CORE_DIR
    
    cd $WP_CORE_DIR
	wp core download --version=$WP_VERSION

	wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	rm -rf $WP_TESTS_DIR
	mkdir -p $WP_TESTS_DIR
	cd $WP_TESTS_DIR
	svn co --quiet http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/

	wget -nv -O wp-tests-config.php http://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" wp-tests-config.php
}

install_db() {
	# parse DB_HOST for port or socket references
	# local PARTS=(${DB_HOST//\:/ })
	# local DB_HOSTNAME=${PARTS[0]};
	# local DB_SOCK_OR_PORT=${PARTS[1]};
	# local EXTRA=""
	#
	# if ! [ -z $DB_HOSTNAME ] ; then
	# 	if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
	# 		EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
	# 	elif ! [ -z $DB_SOCK_OR_PORT ] ; then
	# 		EXTRA=" --socket=$DB_SOCK_OR_PORT"
	# 	elif ! [ -z $DB_HOSTNAME ] ; then
	# 		EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
	# 	fi
	# fi
	#
	# # create database
	# mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA

	mysql --user="$DB_USER" --password="$DB_PASS" -e "DROP DATABASE $DB_NAME"
	mysql --user="$DB_USER" --password="$DB_PASS" -e "CREATE DATABASE $DB_NAME"
}

install_wp
install_test_suite
install_db
