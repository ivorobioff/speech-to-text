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
            if (isset($_FILES['speech'])) {

                $flac = $_FILES['speech']['tmp_name'];

                require_once 'GoogleService.php';
                require_once 'BingService.php';

               try {
                   $result = (new GoogleService())->toText($flac);
                   echo '<p><b>Google:</b> '.$result.'</p>';
               } catch (RuntimeException $exception){
                   echo '<p><b>Google:</b> <span style="color: red">'.$exception->getMessage().'</span></p>';
               }

                $wav = uniqid().'.wav';

                $result = exec('ffmpeg -i '.$flac.' '.$wav.' 2>&1');

                if (strpos($result, 'audio:')) {
                    try {
                        $result = (new BingService())->toText(__DIR__.'/'.$wav);
                        echo '<p><b>Microsoft:</b> '.$result.'</p>';
                    } catch (RuntimeException $exception) {
                        echo '<p><b>Microsoft:</b> <span style="color: red">'.$exception->getMessage().'</span></p>';
                    }
                } else {
                    echo '<p><b>Microsoft:</b> <span style="color: red">Unable to convert the audio file</span></p>';
                }

                @unlink(__DIR__.'/'.$wav);
            }
        ?>
    </body>
</html>