<?php

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <link rel="icon" type="image/png" href="inc/img/icon.png"/>
    <link href='templates/calendar/fullcalendar/fullcalendar.css' rel='stylesheet'/>
    <link href='templates/calendar/fullcalendar/fullcalendar.print.css' rel='stylesheet' media='print'/>
    <link rel="stylesheet" type="text/css" id="mainTheme"
          href="../vendor/cmskit/jquery-ui/themes/<?php echo end($_SESSION[$projectName]['config']['theme']) ?>/jquery-ui.css"/>
    <script src="../vendor/cmskit/jquery-ui/jquery.min.js"></script>
    <script src="../vendor/cmskit/jquery-ui/jquery-ui.js"></script>
    <script src='templates/calendar/fullcalendar/fullcalendar.js'></script>

    <?php

    if (!isset($_SESSION[$projectName]['client']['json'])) {
        echo '<script src="../vendor/cmskit/jquery-ui/plugins/json3.min.js"></script>';
    }

    echo '
	<script>
		var projectName="' . $projectName . '",
			objectName=' . ($objectName ? '"' . $objectName . '"' : 'false') . ',
			objectId = "",
			settings = ' . @json_encode($_SESSION[$projectName]['settings']) . ' || {},
			store = {},
			theme = "' . end($_SESSION[$projectName]['config']['theme']) . '",
			lang = "' . $lang . '",
			userId = "' . $_SESSION[$projectName]['special']['user']['id'] . '",
			userProfiles = ' . json_encode($_SESSION[$projectName]['special']['user']['profiles']) . ',
			langLabels = {' . $jsLangLabels . '},
			client = JSON.parse(\'' . json_encode($_SESSION[$projectName]['client']) . '\'),
		    config = ' . json_encode($objects[$objectName]['config']['calendar']) . ',
		    res = "";
		for(var r in config.resources){if(r==objectName){res=r}}
	</script>
	';
    ?>

    <script src="templates/calendar/locales/<?php echo $lang ?>.js"></script>
    <script src="templates/calendar/js/functions.js"></script>

    <style>

        body {
            margin: 20px 20px 20px 20px;
            text-align: center;
            font-size: 13px;
            font-family: "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
        }

        .subhead {
            float: left;
        }

        #calendarhead {
            height: 100px;
            width: 100%;

        }

        #subhead2 {
            margin-left: 50px;
        }

        #subhead3 {
            float: right;
        }

        #calendar {
            border-top: 1px solid #eee;
            margin-top: 5px;
            padding-top: 5px;
            clear: both;
            width: 100%;
            z-index: 1;
            position: absolute;
            top: 80px;
            background-color: #fff;
        }

        #dialog {
            z-index: 10;
            display: none;
        }

        #dialogbody {
            border: 0px none;
        }

        select {
            padding: 5px;
        }

        .fc-resourceName {
            min-width: 50px;
        }

        .fc-resourceName .ui-icon {
            display: inline-block;
            cursor: pointer;
            margin-left: 2px;
        }

        .fc-resourceName span {
            display: none;
        }

        .fc-event-inner {
            cursor: pointer;
        }

        #filterSelectBox {
            display: inline-block;
        }
    </style>
</head>
<body>

<div id="calendarhead">
    <div class="subhead" id="subhead3">
        <?php echo $uwizSelect ?>
        <button id="logoutButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary"
                onclick="logout()" type="button" role="button" aria-disabled="false">
            <span class="ui-button-icon-primary ui-icon ui-icon-power"></span>
            <span class="ui-button-text"><?php echo L('logout') ?></span>
        </button>
    </div>

    <div class="subhead" id="subhead1">
        <?php
        // draw Logo if available
        if (file_exists($projectPath . '/objects/logo.png')) {
            echo '<img id="logo" style="height:27px;float:left;margin:0 10px 0 0;" src="' . $projectPath . '/objects/logo.png" />';
        }
        ?>
    </div>
    <div class="subhead" id="subhead2">
        <?php
        // draw Object-Selector
        echo $dropdowns['objectSelect'];

        // draw Template-Selector if needed
        echo $dropdowns['tplSelect'];
        ?>

    </div>

</div>

<div id="calendar">
    <div id="calendarbody"></div>
</div>

<div id="dialog">
    <iframe id="dialogbody" src="about:blank"></iframe>
</div>
</body>
</html>
