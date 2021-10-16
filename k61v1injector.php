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
 * Qin F21 Pro (k61v1) Injector
 * 
 * https://github.com/MlgmXyysd/k61v1injector
 * 
 * Exploit the vulnerability to install arbitrary applications in k61v1 without ROOT
 * 
 * @author MlgmXyysd
 * @version 1.1
 * 
 * All copyright in the software is not allowed to be deleted
 * or changed without permission.
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
            if ($d["manufacturer"] === "DuoQin" && $d["brand"] === "Qin" && $d["device"] === "k61v1_64_bsp") {
                $t[] = array($d["serial"], $d["transport"]);
            }
        }
    }
    return $t;
}

$v = "1.0";

print("\033[32m********************************\033[0m" . PHP_EOL);
print("\033[32m* Qin F21 Pro (k61v1) Injector *\033[0m" . PHP_EOL);
print("\033[32m* By MlgmXyysd                 *\033[0m" . PHP_EOL);
print("\033[32m********************************\033[0m" . PHP_EOL);
print("- Version " . $v . PHP_EOL);

if (!isset($argc)) {
    exit("! \033[31mError: You should run from command line!\033[0m" . PHP_EOL);
}

if ($argc < 2) {
    echo("- \033[33mUsage: k61v1injector.php <PACKAGE>\033[0m" . PHP_EOL);
    exit("! \033[31mError: No package specified.\033[0m" . PHP_EOL);
}

if (substr($argv[1], -4) !== ".apk") {
    exit("! \033[31mError: Filename doesn't end .apk: " . $argv[1] . "\033[0m" . PHP_EOL);
}

if (!file_exists($argv[1])) {
    exit("! \033[31mError: Failed to stat " . $argv[1] . ": No such file or directory.\033[0m" . PHP_EOL);
}

$p = "53bd6c7fdf78d534a2217e706736ff07";

$a = new ADB(__DIR__ . DIRECTORY_SEPARATOR . "libraries");

echo("- Detecting device..." . PHP_EOL);
$t = parseDeviceList($a);

if (count($t) === 0) {
    echo("* \033[33mNo Qin F21 Pro device detected, please connect to PC");
    while (true) {
        if (count($t) !== 0) {
            echo("\033[0m" . PHP_EOL);
            break;
        }
        echo(".");
        sleep(0.1);
        $t = parseDeviceList($a);
    }
}
echo("- " . count($t) . " device(s) detected." . PHP_EOL);

$n = "com.duoqin.promarket";

foreach ($t as $d) {
    $i = $a -> getDeviceId($d[1], true);
    echo("- Processing device " . $d[0] . "(" . $d[1] . ")..." . PHP_EOL);

    $a -> setScreenDensity("reset", $i);
    $a -> setScreenSize("reset", $i);

    if (!$a -> getScreenState($i)) {
        echo("- Screen is off, trying to unlock the screen." . PHP_EOL);
        $a -> sendInput("keyevent", "26", $i);
    }
    $a -> sendInput("keyevent", "3", $i);
    $a -> sendInput("swipe", "0 640 0 0", $i);
    if ($a -> getCurrentActivity($i)[0] === "NotificationShade") {
        echo("* \033[33mScreen is locked and cannot be unlocked automatically.\033[0m" . PHP_EOL);
        echo("* \033[33mPlease unlock the screen manually");
        while (true) {
            if ($a -> getCurrentActivity($i)[0] !== "NotificationShade") {
                echo("\033[0m" . PHP_EOL);
                break;
            }
            echo(".");
            sleep(0.1);
        }
    }
    $a -> sendInput("keyevent", "3", $i);
    
    echo("- Detecting vulnerability..." . PHP_EOL);

    $k = $a -> getPackage($n, $i);
    
    if (!$k) {
        if (!$a -> runAdbJudge($i . "shell market install-existing " . $n)) {
            echo("! \033[31mError: ProMarket package not found.\033[0m" . PHP_EOL);
            continue;
        }
        $k = $a -> getPackage($n, $i);
    }
    
    $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $k, true)[0])[0];
    
    if ($m !== $p) {
        echo("- Vulnerability not found, injecting..." . PHP_EOL);
        $r = generateRandomStr();
        $a -> runAdb($i . "shell mkdir -p /sdcard/" . $r);
        $a -> runAdb($i . "push \"" . __DIR__ . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "ProMarketExploit_MlgmXyysd_signed.apk\" /sdcard/" . $r . "/");
        $a -> openDocumentUI($r, $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            sleep(0.1);
        }
        $a -> sendInput("keyevent", "66", $i);
        $a -> sendInput("keyevent", "66", $i);
        while (!$a -> getCurrentActivity($i)[0]) {
            sleep(0.1);
        }
        $a -> sendInput("keyevent", "66", $i);
        $a -> sendInput("keyevent", "22", $i);
        $a -> sendInput("keyevent", "66", $i);
        while ($a -> getCurrentActivity($i)[1] === "com.android.packageinstaller.InstallInstalling") {
            sleep(0.1);
        }
        $a -> sendInput("keyevent", "3", $i);
        $a -> runAdb($i . "shell rm -rf /sdcard/" . $r);
        $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $a -> getPackage($n, $i), true)[0])[0];
        if ($m !== $p) {
            echo("! \033[31mError: Inject failed.\033[0m" . PHP_EOL);
            continue;
        }
    } else {
        echo("- Vulnerability found." . PHP_EOL);
    }

    echo("- Exploiting..." . PHP_EOL);
    $a -> runAdb($i . "shell pm clear " . $n);
    $a -> runAdb($i . "shell am start -n " . $n . "/.MarketActivity");
    while ($a -> getCurrentActivity($i)[1] !== $n . ".MarketActivity") {
        sleep(0.1);
    }
    while (imagecolorat(imagecreatefromstring($a -> getScreenshotPNG($i)), 400, 146) !== 2797016) {
        sleep(0.1);
    }

    echo("- Transferring package..." . PHP_EOL);
    $r = generateRandomStr();
    $a -> runAdb($i . "push \"" . $argv[1] . "\" /sdcard/" . $r . ".apk");
    $j = $a -> runAdb($i . "shell md5sum /sdcard/" . $r . ".apk", true);
    if (!$a -> judgeOutput($j)) {
        echo("! \033[31mError: Package transfer failed.\033[0m" . PHP_EOL);
        continue;
    }
    $j = explode("  ", $j[0])[0];

    echo("- Testing operation compatibility..." . PHP_EOL);
    $a -> sendInput("tap", "400 170", $i);
    while (!$a -> getCurrentActivity($i)[0]) {
        sleep(0.1);
    }
    $c = $a -> getCurrentActivity($i);
    if ($c[0] !== $n) {
        echo("- First app in the list is installed, uninstalling " . $c[0] . "..." . PHP_EOL);
        $a -> runAdb($i . "shell pm uninstall " . $c[0]);
        $a -> runAdb($i . "shell am start -n " . $n . "/.MarketActivity");
        while (imagecolorat(imagecreatefromstring($a -> getScreenshotPNG($i)), 400, 146) !== 2797016) {
            sleep(0.1);
        }
        $a -> sendInput("tap", "400 170", $i);
    } else {
        echo("- Looks good, let's go on." . PHP_EOL);
    }

    while (!$a -> runAdbJudge($i . "shell \"ls -1 /sdcard/Android/data/" . $n . "/files/Download 2>/dev/null\"")) {
        sleep(0.1);
    }

    echo("- Triggering installation operation..." . PHP_EOL);
    $e = "/sdcard/Android/data/" . $n . "/files/Download/" . $a -> runAdb($i . "shell ls -1 /sdcard/Android/data/" . $n . "/files/Download")[0][0];
    $m = explode("  ", $a -> runAdb($i . "shell md5sum " . $e, true)[0])[0];

    echo("- Replacing " . $m . " with " . $j . "..." . PHP_EOL);
    $a -> runAdb($i . "shell rm -f " . $e);
    $a -> runAdb($i . "shell mv -f /sdcard/" . $r . ".apk " . $e);
    $a -> sendInput("keyevent", "3", $i);

    echo("- \033[32mDone.\033[0m" . PHP_EOL);
}

echo("- \033[32mAll done, have fun :)\033[0m" . PHP_EOL);
?>
