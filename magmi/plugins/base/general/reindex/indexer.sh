#!/bin/bash

BASEDIR=$(dirname $0)
echo "Usage: $0 filename"
sleep 600
cd  /home/joachim/shell/public_html
/usr/local/bin/php /home/joachim/public_html/shell/pricerule.php
/usr/local/bin/php /home/joachim/public_html/shell/indexer.php --reindexall
/usr/local/bin/php /home/joachim/public_html/shell/turpentine.php