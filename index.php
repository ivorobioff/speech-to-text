<html>
    <head>
        <title>Speech</title>
    </head>
    <body>
        <form action="/" method="POST" enctype="multipart/form-data">
            <label>Select the audio file: <input type="file" name="speech" /></label>
            <br />
            <br />
            <button type="submit">Proceed</button>
        </form>

        <?php
            require_once 'GoogleService.php';
            require_once 'BingService.php';

            if (isset($_FILES['speech'])) {

                $tmp = $_FILES['speech']['tmp_name'];

                $flac = __DIR__.'/'.uniqid().'.flac';

                $result = exec('ffmpeg -i '.$tmp.' -ar 16000 -ac 1 -acodec flac '.$flac.' 2>&1');

                if (strpos($result, 'audio:')){

                    try {
                        $result = (new GoogleService())->toText($flac);
                        echo '<p><b>Google:</b> '.$result.'</p>';
                    } catch (RuntimeException $exception){
                        echo '<p><b>Google:</b> <span style="color: red">'.$exception->getMessage().'</span></p>';
                    }

                    $wav = __DIR__.'/'.uniqid().'.wav';

                    $result = exec('ffmpeg -i '.$flac.' '.$wav.' 2>&1');

                    if (strpos($result, 'audio:')) {
                        try {
                            $result = (new BingService())->toText($wav);
                            echo '<p><b>Microsoft:</b> '.$result.'</p>';
                        } catch (RuntimeException $exception) {
                            echo '<p><b>Microsoft:</b> <span style="color: red">'.$exception->getMessage().'</span></p>';
                        }
                    } else {
                        echo '<p><b>Microsoft:</b> <span style="color: red">Unable to convert the audio file</span></p>';
                    }

                    @unlink($wav);
                    @unlink($flac);
                } else {
                    echo '<p><span style="color: red">Incorrect FLAC was given</span></p>';
                }
            }
        ?>
    </body>
</html>