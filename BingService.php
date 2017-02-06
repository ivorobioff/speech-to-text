<?php

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class BingService
{
    const API_KEY = '4bd5764d0eca4ca48368abf31e065be4';

    /**
     * @param string $file
     * @param string $lang
     * @return string
     */
    public function toText($file, $lang = 'nl-NL')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Ocp-Apim-Subscription-Key: '.self::API_KEY,
            'Content-Length: 0'
        ]);

        $token = curl_exec($ch);

        if ($token === false){
            throw new RuntimeException('Unable to retrieve token');
        }

        curl_close($ch);

        $ch = curl_init();

        $parameters = [
            'locale' => $lang,
            'version' => '3.0',
            'requestid' => 'b2c95ede-97eb-4c88-81e4-80f32d6aee54',
            'instanceid' => 'b2c95ede-97eb-4c88-81e4-80f32d6aee54',
            'format' => 'json',
            'appid' => 'D4D52672-91D7-4C74-8AD8-42B1D98141A5',
            'scenarios' => 'smd',
            'device.os' => 'linux'
        ];

        curl_setopt($ch, CURLOPT_URL, 'https://speech.platform.bing.com/recognize?'.http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: audio/wav; codec="audio/pcm"; samplerate=16000',
            'Authorization: Bearer '.$token
        ]);

        $result = curl_exec($ch);

        if ($result === false){
            throw new RuntimeException('Unknown error');
        }

        $json = json_decode($result, true);

        if ($json === null){
            throw new RuntimeException('Error: '.$result);
        }

        if (isset($json['results'][0]['lexical'])) {
            return $json['results'][0]['lexical'];
        }

        throw new RuntimeException('Unknown JSON: '.print_r(json_encode($json), true));
    }
}