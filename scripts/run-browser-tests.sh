#!/usr/bin/env bash

# Run each Browser test file as its own pest invocation. testbench-dusk
# restarts the PHP built-in server between test classes, and the socket
# sometimes ends up in TIME_WAIT, wedging the next class's server. A
# fresh pest process per file sidesteps that — and a hard kill between
# runs ensures no chromedriver or Chrome-for-Testing zombie survives.

set -u

cleanup() {
    pkill -9 -f 'chromedriver' 2>/dev/null || true
    pkill -9 -f 'Chrome for Testing' 2>/dev/null || true
    pkill -9 -f 'php -S 127.0.0.1:8099' 2>/dev/null || true
}

trap cleanup EXIT

failed=0

for file in tests/Browser/*Test.php; do
    echo ""
    echo "──── $(basename "$file") ────"
    cleanup
    sleep 1
    ./vendor/bin/pest -c phpunit.dusk.xml "$file"
    status=$?
    if [ $status -ne 0 ]; then
        failed=1
    fi
done

exit $failed
