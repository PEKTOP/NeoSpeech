<?php

namespace NeoSpeech;

use \DOMDocument;
use \GuzzleHttp\Client as HttpClient;
use \InvalidArgumentException;
use \SimpleXMLElement;

/**
 * NeoSpeech REST Client
 * @package NeoSpeech
 */
class Client
{
    const NEOSPEECH_REST_CLIENT = 'https://tts.neospeech.com/rest_1_1.php';

    /**
     * @var array
     */
    protected $voices = [
        'paul'       => 'TTS_PAUL_DB',
        'kate'       => 'TTS_KATE_DB',
        'julie'      => 'TTS_JULIE_DB',
        'neobridget' => 'TTS_NEOBRIDGET_DB',
        'neovioleta' => 'TTS_NEOVIOLETA_DB',
    ];

    /**
     * @var array
     */
    protected $formats = [
        'wav'     => 'FORMAT_WAV',     // 16bit linear PCM Wave
        'pcm'     => 'FORMAT_PCM',     // 16bit linear PCM
        'mulaw'   => 'FORMAT_MULAW',   // 8bit Mu-law PCM
        'alaw'    => 'FORMAT_ALAW',    // 8bit A-law PCM
        'adpcm'   => 'FORMAT_ADPCM',   // 4bit Dialogic ADPCM
        'ogg'     => 'FORMAT_OGG',     // Ogg Vorbis
        '8bitwav' => 'FORMAT_8BITWAV', // 8bit unsigned linear PCM Wave
        'awav'    => 'FORMAT_AWAV',    // 8bit A-law PCM Wave
        'muwav'   => 'FORMAT_MUWAV',   // 8bit Mu-law PCM Wave
    ];

    /**
     * @var array
     */
    protected $rates = [8, 16];

    private $email;
    private $account_id;
    private $login_key;
    private $password;
    private $voice;
    private $audio_format;
    private $audio_rate;

    /**
     * @param string $email
     * @param string $account_id
     * @param string $login_key
     * @param string $password
     * @param string $voice
     * @param string $audio_format
     * @param int $audio_rate
     */
    public function __construct($email, $account_id, $login_key, $password,
                                $voice = 'paul', $audio_format = 'wav', $audio_rate = 16)
    {
        $this->email = $email;
        $this->account_id = $account_id;
        $this->login_key = $login_key;
        $this->password = $password;
        $this->voice = $this->setVoice($voice);
        $this->audio_format = $this->setFormat($audio_format);
        $this->audio_rate = $this->setRate($audio_rate);
    }

    /**
     * @return array
     */
    public function getAvailableFormats()
    {
        return $this->formats;
    }

    /**
     * @return array
     */
    public function getAvailableRate()
    {
        return $this->rates;
    }

    /**
     * @return array
     */
    public function getAvailableVoice()
    {
        return $this->voices;
    }

    /**
     * @param string $key
     * @return string
     */
    public function setFormat($key)
    {
        return $this->setByKey($key, $this->formats, 'Format of audio');
    }

    /**
     * @param int $value
     * @return int
     * @throws \InvalidArgumentException
     */
    public function setRate($value)
    {
        if (in_array($value, $this->rates)){
            return $value;
        }

        throw new InvalidArgumentException('Rate of audio is incorrect');
    }

    /**
     * @param string $key
     * @return string
     */
    public function setVoice($key)
    {
        return $this->setByKey($key, $this->voices, 'Voice');
    }

    /**
     * @param $text
     * @return array
     */
    public function convertSimple($text)
    {
        $dom = new DOMDocument('1.0', 'utf-8');

        $convert_simple = $dom->createElement('ConvertSimple');

        $email = $dom->createElement('email', $this->email);
        $account_id = $dom->createElement('accountId', $this->account_id);
        $login_key = $dom->createElement('loginKey', $this->login_key);
        $login_password = $dom->createElement('loginPassword', $this->password);
        $voice = $dom->createElement('voice', $this->voice);
        $output_format = $dom->createElement('outputFormat', $this->audio_format);
        $sample_rate = $dom->createElement('sampleRate', $this->audio_rate);
        $text = $dom->createElement('text', $text);

        $convert_simple->appendChild($email);
        $convert_simple->appendChild($account_id);
        $convert_simple->appendChild($login_key);
        $convert_simple->appendChild($login_password);
        $convert_simple->appendChild($voice);
        $convert_simple->appendChild($output_format);
        $convert_simple->appendChild($sample_rate);
        $convert_simple->appendChild($text);

        $dom->appendChild($convert_simple);

        $body = $dom->saveHTML();

        return $this->createMessage($this->sendRequest($body));
    }

    /**
     * @param $number
     * @return array
     */
    public function getConversionStatus($number)
    {
        $dom = new DOMDocument('1.0', 'utf-8');

        $get_conversion_status = $dom->createElement('GetConversionStatus');

        $email = $dom->createElement('email', $this->email);
        $account_id = $dom->createElement('accountId', $this->account_id);
        $conversion_number = $dom->createElement('conversionNumber', $number);

        $get_conversion_status->appendChild($email);
        $get_conversion_status->appendChild($account_id);
        $get_conversion_status->appendChild($conversion_number);

        $dom->appendChild($get_conversion_status);

        $body = $dom->saveHTML();

        return $this->createMessage($this->sendRequest($body));
    }

    /**
     * @param array|SimpleXMLElement $source
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function createMessage($source)
    {
        if (is_array($source)) {
            return $source;
        }

        if ($source instanceof SimpleXMLElement) {
            $message = [];
            foreach ($source->attributes() as $key => $value) {
                $message[(string)$key] = (string)$value;
            }
            return $message;
        }

        throw new InvalidArgumentException('Unable to create message');
    }

    /**
     * @param string $body
     * @return array|SimpleXMLElement
     */
    protected function sendRequest($body)
    {
        $http_client = new HttpClient();

        try {
            $response = $http_client->post(self::NEOSPEECH_REST_CLIENT, [
                'body' => $body,
            ]);

            return $response->xml();
        } catch (\Exception $exception) {
            $trace = explode("\n", $exception->getTraceAsString());

            $stack_trace = [];

            foreach ($trace as $line)
            {
                $stack_trace[] = $line;
            }

            return [
                'http_error' => [
                    'type'    => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $stack_trace,
                ],
            ];
        }
    }

    /**
     * @param string $key
     * @param array $array
     * @param string $argument_name
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function setByKey($key, array $array, $argument_name)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        if (is_string($argument_name)) {
            throw new InvalidArgumentException(sprintf('%s is incorrect', $argument_name));
        }

        throw new InvalidArgumentException('Name of argument should be string value');
    }

}
