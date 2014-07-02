#!/bin/bash

BASEDIR=$(dirname $0)
echo "Usage: $0 filename"
sleep 600
cd  /home/joachim/shell/public_html
/usr/local/bin/php /home/joachim/public_html/shell/pricerule.php
/usr/local/bin/php /home/joachim/public_html/shell/indexer.php --reindexall
/usr/local/bin/php /home/joachim/public_html/shell/turpentine.php
find /home/joachim/public_html/var/backups/ -type f -mtime +2 ! -name .htaccess -delete
find /home/joachim/public_html/media/catalog/product/cache/ -type f -mtime +30 -delete
find /home/joachim/public_html/var/report/ -type f  -mtime +2 -delete
# /home/joachim/public_html/shell/delete_unused_images.sh cleanup