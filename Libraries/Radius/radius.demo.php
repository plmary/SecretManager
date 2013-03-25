<?php

require_once('radius.class.php');

?>
<html>
    <head>
        <title>
            Pure PHP radius class demo
        </title>
    </head>
    <body>
        <?php
        if ((isset($_POST['user'])) && ('' != trim($_POST['user'])))
        {
            $radius = new Radius('192.168.56.101', 'secret');

//            $radius->SetNasIpAddress('192.168.56.101'); // Needed for some devices, and not auto_detected if PHP not runned through a web server
            // Enable Debug Mode for the demonstration
            //$radius->SetDebugMode(TRUE);

            if ($radius->AccessRequest($_POST['user'], $_POST['pass']))
            {
                echo "<strong>Authentication accepted.</strong>";
            }
            else
            {
                echo "<strong>Authentication rejected.</strong>";
            }
            echo "<br />";

            echo "<br /><strong>GetReadableReceivedAttributes</strong><br />";
            echo $radius->GetReadableReceivedAttributes();

            echo "<br />";
            echo "<a href=\"".$_SERVER['PHP_SELF']."\">Reload authentication form</a>";
        }
        else
        {
            ?>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                User: <input name="user" type="text" value="user" />
                <br />

                Pass: <input name="pass" type="text" value="pass" /> (text type for educational purpose only) <!-- type="text" for educational purpose only ! -->
                <br />
                
                <input name="submit" type="submit" value="Check authentication" />
            </form>
            <?php
        }
        ?>
    </body>
<html>
