<?php
/**
 * 
 *    Copyright (C) 2002-2022 MlgmXyysd All Rights Reserved.
 *    Copyright (C) 2013-2022 MeowCat Studio All Rights Reserved.
 *    Copyright (C) 2020-2022 Meow Mobile All Rights Reserved.
 * 
 */

/**
 * 
 * Qin F21 Pro Injector
 * 
 * https://github.com/MlgmXyysd/F21ProInjector
 * 
 * Exploit the vulnerability to install arbitrary
 * applications in F21 Pro without ROOT
 * 
 * Environment requirement:
 *   - php-adb library
 *   - PHP 8 + GD Extension
 * 
 * @author MlgmXyysd
 * @version 1.2
 * 
 * All copyright (and link, etc.) in this software is not
 * allowed to be deleted or changed without permission.
 * 
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "adb.php";

use MeowMobile\ADB;

function generateRandomStr($l = 16) {
    $T = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $s = "";
    for ($i = 1; $i <= $l; $i++) {
        $s .= $T[mt_rand(0, strlen($T) - 1)];
    }
    return $s;
}

function parseDeviceList($a) {
    $s = $a -> refreshDeviceList();
    $t = array();
    foreach ($s as $d) {
        if ($d["status"] === $a::CONNECT_TYPE_DEVICE) {
            if ($d["manufacturer"] === "DuoQin" && $d["brand"] === "Qin" && str_contains($d["device"], "k61v1")) {
                $t[] = array($d["serial"], $d["transport"]);
            }
        }
    }
    return $t;
}

function logf($m = "", $c = "", $p = "-", $t = "I") {
	switch (strtoupper($c)) {
		case "G":
			$c = "\033[32m";
			break;
		case "R":
			$c = "\033[31m";
			break;
		case "Y":
			$c = "\033[33m";
			break;
		default:
			$c = "";
	}
	switch (strtoupper($t)) {
		case "W":
			$t = "WARN";
			break;
		case "E":
			$t = "ERROR";
			break;
		case "I":
		default:
			$t = "INFO";
	}
	print(date("[Y-m-d] [H:i:s]") . " [" . $t . "] " . $p . " " . $c . $m . "\033[0m" . PHP_EOL);
}

$v = "1.2";

logf("*******************************", "g");
logf("* Duoqin Qin F21 Pro Injector *", "g");
logf("* By MlgmXyysd    Version " . $v . " *", "g");
logf("*******************************", "g");
logf("GitHub: https://github.com/MlgmXyysd");
logf("XDA: https://forum.xda-developers.com/m/mlgmxyysd.8430637");
logf("Twitter: https://twitter.com/realMlgmXyysd");
logf("PayPal: https://paypal.me/MlgmXyysd");
logf("My Website: https://www.neko.ink/");
logf("Telegram Group: https://t.me/+Mi18E90aOgUwZjNl");
logf("*******************************", "g");

if (!isset($argc)) {
	logf("Error: You should run from command line!", "r", "!", "e");
	exit(1);
}

if ($argc < 2) {
	logf("Usage: k61v1injector.php <PACKAGE>", "y");
	logf("Error: No package specified.", "r", "!", "e");
    exit(1);
}

if (substr($argv[1], -4) !== ".apk") {
	logf("Error: Filename doesn't end .apk: " . $argv[1], "r", "!", "e");
    exit(1);
}

if (!file_exists($argv[1])) {
	logf("Error: Failed to stat " . $argv[1] . ": No such file or directory.", "r", "!", "e");
    exit(1);
}

$y = "7ef34e209382f589a5456c4cf8279b75";
$p = "53bd6c7fdf78d534a2217e706736ff07";

logf("Starting ADB server...");

$a = new ADB(__DIR__ . DIRECTORY_SEPARATOR . "libraries");

logf("Detecting device...");

$t = parseDeviceList($a);

if (count($t) === 0) {
	logf("No Qin F21 Pro device detected, please connect to PC.", "y", "*");
    while (count($t) === 0) {
        usleep(10000);
        $t = parseDeviceList($a);
    }
}

logf(count($t) . " device(s) detected.");

$n = "com.duoqin.promarket";
$z = "com.android.packageinstaller";

foreach ($t as $d) {
    $i = $a -> getDeviceId($d[1], true);
	logf("Processing device " . $d[0] . "(" . $d[1] . ")...");

    $a -> setScreenDensity("reset", $i);
    $a -> setScreenSize("reset", $i);

    if (!$a -> getScreenState($i)) {
		logf("Screen is off, trying to unlock the screen.");
        $a -> sendInput("keyevent", "26", $i);
    }
    $a -> sendInput("keyevent", "3", $i);
    $a -> sendInput("swipe", "0 640 0 0", $i);
    if ($a -> getCurrentActivity($i)[0] === "NotificationShade") {
		logf("Screen is locked and cannot be unlocked automatically.", "y", "*");
		logf("Please unlock the screen manually.", "y", "*");
        while ($a -> getCurrentActivity($i)[0] === "NotificationShade") {
            usleep(10000);
        }
    }
    $a -> sendInput("keyevent", "3", $i);
    
	logf("Detecting vulnerability (1/2)...");
	
    $k = $a -> getPackage($z, $i);
    
    $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $k, true)[0])[0];
    
    if ($m !== $y) {
		logf("Vulnerability not found, injecting...");
        $r = generateRandomStr();
        $a -> runAdb($i . "shell mkdir -p /sdcard/" . $r);
        $a -> runAdb($i . "push \"" . __DIR__ . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "PackageInstallerExploit_MlgmXyysd_signed.apk\" /sdcard/" . $r . "/" . $r . ".apk");
        $a -> openDocumentUI($r, $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "20", $i);
        $a -> sendInput("keyevent", "66", $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "66", $i);
        $a -> sendInput("keyevent", "22", $i);
        $a -> sendInput("keyevent", "66", $i);
        while ($a -> getCurrentActivity($i)[1] === $z . ".InstallInstalling") {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "3", $i);
        $a -> runAdb($i . "shell rm -rf /sdcard/" . $r);
        $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $a -> getPackage($z, $i), true)[0])[0];
        if ($m !== $y) {
			logf("Error: Inject failed.", "r", "!", "e");
            continue;
        }
    } else {
		logf("Vulnerability found (1/2).");
    }
    
	logf("Detecting vulnerability (2/2)...");
	
    $k = $a -> getPackage($n, $i);
    
    if (!$k) {
        if (!$a -> runAdbJudge($i . "shell market install-existing " . $n)) {
			logf("Error: ProMarket package not found.", "r", "!", "e");
            continue;
        }
        $k = $a -> getPackage($n, $i);
    }
    
    $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $k, true)[0])[0];
    
    if ($m !== $p) {
		logf("Vulnerability not found, injecting...");
        $r = generateRandomStr();
        $a -> runAdb($i . "shell mkdir -p /sdcard/" . $r);
        $a -> runAdb($i . "push \"" . __DIR__ . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "ProMarketExploit_MlgmXyysd_signed.apk\" /sdcard/" . $r . "/" . $r . ".apk");
        $a -> openDocumentUI($r, $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "20", $i);
        $a -> sendInput("keyevent", "66", $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "66", $i);
        $a -> sendInput("keyevent", "22", $i);
        $a -> sendInput("keyevent", "66", $i);
        while ($a -> getCurrentActivity($i)[1] === $z . ".InstallInstalling") {
            usleep(10000);
        }
        $a -> sendInput("keyevent", "3", $i);
        $a -> runAdb($i . "shell rm -rf /sdcard/" . $r);
        $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $a -> getPackage($n, $i), true)[0])[0];
        if ($m !== $p) {
			logf("Error: Inject failed.", "r", "!", "e");
            continue;
        }
    } else {
		logf("Vulnerability found (2/2).");
    }

	logf("Exploiting...");
    $a -> runAdb($i . "shell pm clear " . $n);
    $a -> runAdb($i . "shell am start -n " . $n . "/.MarketActivity");
    while (imagecolorat(imagecreatefromstring($a -> getScreenshotPNG($i)), 400, 146) !== 2797016) {
        usleep(10000);
    }

	logf("Transferring package...");
    $r = generateRandomStr();
    $a -> runAdb($i . "push \"" . $argv[1] . "\" /sdcard/" . $r . ".apk");
    $j = $a -> runAdb($i . "shell md5sum /sdcard/" . $r . ".apk", true);
    if (!$a -> judgeOutput($j)) {
		logf("Error: Package transfer failed.", "r", "!", "e");
        continue;
    }
    $j = explode("  ", $j[0])[0];

	logf("Testing operation compatibility...");
    $a -> sendInput("tap", "400 170", $i);
    while (!$a -> getCurrentActivity($i)[0]) {
        usleep(10000);
    }
    $c = $a -> getCurrentActivity($i);
    if ($c[0] !== $n) {
		logf("First app in the list is installed, uninstalling " . $c[0] . "...");
        $a -> runAdb($i . "shell pm uninstall " . $c[0]);
        $a -> runAdb($i . "shell am start -n " . $n . "/.MarketActivity");
        while (imagecolorat(imagecreatefromstring($a -> getScreenshotPNG($i)), 400, 146) !== 2797016) {
            usleep(10000);
        }
        $a -> sendInput("tap", "400 170", $i);
    } else {
		logf("Looks good, let's go on.");
    }

    while (!$a -> runAdbJudge($i . "shell \"ls -1 /sdcard/Android/data/" . $n . "/files/Download 2>/dev/null\"")) {
        usleep(10000);
    }

	logf("Triggering installation operation...");
    $e = "/sdcard/Android/data/" . $n . "/files/Download/" . $a -> runAdb($i . "shell ls -1 /sdcard/Android/data/" . $n . "/files/Download")[0][0];
    $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $e, true)[0])[0];

	logf("Replacing " . $m . " with " . $j . "...");
    $a -> runAdb($i . "shell rm -f " . $e);
    $a -> runAdb($i . "shell mv -f /sdcard/" . $r . ".apk " . $e);
    $a -> sendInput("keyevent", "3", $i);

	logf("Done.", "g");
}

logf("All done, have fun :)", "g");
?>
