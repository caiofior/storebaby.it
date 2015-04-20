<pre>
<?php
echo shell_exec('ps ax | grep php'); ?>
</pre>
<p>prima<p/>
<pre>
<?
 //exec("rm -Rf ".__DIR__."/../../../../../var/locks");
 //pclose(popen("php ".__DIR__."/../../../../../shell/indexer.php --reindexall 2>&1 > ".__DIR__."/log.txt &","r"));
 //pclose(popen("php ".__DIR__."/../shell/indexer.php --reindexall 2>&1 > ".__DIR__."/log.txt &","r"));
file_put_contents("c.txt","php ".__DIR__."/../shell/indexer.php --reindexall 2>&1 > ".__DIR__."/log.txt &");
?>
</pre>
