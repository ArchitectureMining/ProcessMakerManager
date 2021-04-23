#!/bin/sh


/usr/bin/flock -n /tmp/runjobs.lockfile php -f /var/www/processmaker/ProcessMakerManager/bin/runjobs.php >/dev/null 2>&1
