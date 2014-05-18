NeoSpeech
=========

PHP wrapper for NeoSpeech Text-to-Speech [REST web service](https://tts.neospeech.com/rest_1_1.php)

Requirements
------------

PHP 5.4.0

Install
-------

```
# Install Composer
curl -sS https://getcomposer.org/installer | php
php composer.phar require pektop/neospeech
```

Usage
-----

See `example` folder

Available REST Methods
----------------------

* ConvertSimple
* GetConversionStatus

TODO
----

* ConvertSsml
* ConvertText

Helpers
-------

* `getAvailableFormats` for `setFormat`
* `getAvailableRate` for `setRate`
* `getAvailableVoice` for `setVoice`