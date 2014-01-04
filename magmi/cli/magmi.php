<?php
pclose(popen('php '.__DIR__.DIRECTORY_SEPARATOR.'magmi.cli.php -mode=xcreate > /dev/null 2> /dev/null &','r'));