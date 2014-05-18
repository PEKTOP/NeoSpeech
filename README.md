NeoSpeech
=========

PHP wrapper for NeoSpeech Text-to-Speech REST web service

* web service host: https://tts.neospeech.com/rest_1_1.php
* documentation: https://ws.neospeech.com/NeoSpeech-TTSWS-API-1.1.pdf

Requirements
------------

PHP 5.4.0

Install
-------

```
# Install Composer
curl -sS https://getcomposer.org/installer | php
php composer.phar require pektop/neospeech:dev-master
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

License
-------
