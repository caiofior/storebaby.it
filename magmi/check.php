<pre>
<?php
echo shell_exec('ps wup $(pgrep -f php)'); ?>
</pre>