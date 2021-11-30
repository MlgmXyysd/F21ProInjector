# F21ProInjector
![Version: 1.2](https://img.shields.io/badge/Version-1.2-brightgreen?style=for-the-badge)

Arbitrary application installer for Qin F21 Pro

Exploit the vulnerability to install arbitrary applications in F21 Pro without ROOT.

Feel free pull request if you want :)

## php-adb
The project proudly uses the [php-adb](https://github.com/MlgmXyysd/php-adb) library.

## How to use
1. Download and install PHP 8 for your system from the [official website](https://www.php.net/downloads).
2. Enable GD extension in `php.ini`.
3. Place `adb.php` in [php-adb](https://github.com/MlgmXyysd/php-adb) to the directory.
4. Download [platform-tools](https://developer.android.com/studio/releases/platform-tools) and place them in `libraries`. *Note: Mac OS needs to rename `adb` to `adb-darwin`.*
5. Open the terminal and use PHP interpreter to execute the [script](f21proinjector.php) with the usage.
6. Wait for the script to run.
- p.s. Releases has packaged the required files.

## TO-DOs
- [x] Nothing to do.

## Changelog
- v1.2:
	- Change codename to f21proinjector
    - Support latest version (f21pro 1.3.0, f21proaae 2.0.7)
- v1.1:
    - Fix wrong judgment
- v1.0:
    - First ver

## Acknowledgements
@ailiyishi

## License
No license, you are only allowed to use this project. All copyright (and link, etc.) in this software is not allowed to be deleted or changed without permission. All rights are reserved by [MeowCat Studio](https://github.com/MeowCat-Studio), [Meow Mobile](https://github.com/Meow-Mobile) and [MlgmXyysd](https://github.com/MlgmXyysd).
