<?php
require_once 'Flac.php';

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class GoogleService
{
    const API_KEY = 'AIzaSyA2QQSsaQ7Bc6RO_Hvbwnq2AISSvi_s_jQ';

    /**
     * @param string $file
     * @param string $lang
     * @return string
     */
    public function toText($file, $lang = 'nl-NL')
    {
        $meta = new Flac($file);

        $url = 'https://speech.googleapis.com/v1beta1/speech:syncrecognize?key='.self::API_KEY;

        $data = [
            'config' => [
                'encoding' => 'FLAC',
                'sampleRate' => $meta->streamSampleRate,
                'languageCode' => $lang
            ],
            'audio' => [
                'content' => base64_encode(file_get_contents($file))
            ]
        ];

        $content = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-type: application/json',
            'Content-Length: '.strlen($content)
        ]);

        $result = curl_exec($ch);

        if ($result === false){
            throw new RuntimeException('Unknown error');
        }

        $json = json_decode($result, true);

        if ($json === null){
            throw new RuntimeException('Error: '.$result);
        }

        if (isset($json['results'][0]['alternatives'][0]['transcript'])) {
            return $json['results'][0]['alternatives'][0]['transcript'];
        }

        throw new RuntimeException('Unknown JSON: '.print_r(json_encode($json), true));
    }
}