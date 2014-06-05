<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Say>Hello Monkey</Say>
    <Play>http://demo.twilio.com/hellomonkey/monkey.mp3</Play>
    <Gather numDigits="1" action="hello-monkey-handle-key.php" method="POST">
        <Say>To speak to a real monkey, press 1.  Press any other key to start over.</Say>
    </Gather>
</Response>
