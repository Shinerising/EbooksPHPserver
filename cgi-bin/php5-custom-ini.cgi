#!/bin/sh
export PHP_FCGI_CHILDREN=3
exec /hsphere/shared/php5/bin/php-cgi -c /hsphere/local/home/c324132/ebooks.wayshine.us/cgi-bin/php.ini