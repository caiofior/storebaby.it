<?php
pclose(popen('nohup php -q '.__DIR__.'/../cli/magmi.cli.php & > /dev/null ','r'));
