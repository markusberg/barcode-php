<?php

class Page {
    private $title;

    function setTitle($title) {
        $this->title = $title;
    }

    function printMenu() {
        print "    </head>\n";
        print "\n";
        print "    <body>\n";
    }

    function printFooter() {
        echo "    </body>\n";
        echo "</html>\n";
    }

    function printHeader() {
        print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
';
        print '    <title>' . $this->title . "</title>\n";
    }

}
?>
