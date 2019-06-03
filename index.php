<?php
/*
 * Simple Gallery (c) 2018 Cassiano Martin
 * 
 * Uses LigthGallery JS plugin (c) 2018 Sachin N; Licensed GPLv3
 * http://sachinchoolur.github.io/lightGallery/
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
 * AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 */

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

opcache_reset();

function scanFiles()
{
    $images_directory = "photos";
    $thumbs_directory = "thumbs";
    
    $listdir = urldecode(filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_SPECIAL_CHARS));
    $dirdata = [];
    $filedata = [];

    // strip multiple slashes/dots to single one
    $listdir = trim($listdir, '/');
    $listdir = str_replace('\\', '/', $listdir);
    $listdir = preg_replace('|\.{2,}|', '.', $listdir);
    $listdir = str_replace('./', '', $listdir);

    // add parent folder button
    if($listdir)
    {
        $last = rtrim(str_replace(basename($listdir), '', $listdir), '/');
        $dirdata[] = '<a class="" href="?dir='.urlencode(ltrim($last, '/')).'">'.
                    '<img class="img-responsive" src="img/folder.png">'.
                    '</a>';
    }

    foreach(new DirectoryIterator($images_directory."/".$listdir) as $fileinfo)
    {
        if($fileinfo->isDir() && !$fileinfo->isDot())
        {
            $dirdata[] = '<a class="" href="?dir='.urlencode(ltrim($listdir.'/'.$fileinfo->getFilename(), '/')).'">'.
                         '<img class="img-responsive" src="img/folder.png">'.
                         '</a>';
        }
        else
        {
            // common image files
            $type = (new SplFileInfo($fileinfo->getPathname()))->getExtension();

            if(in_array(strtolower($type), ['jpg','png','gif','webp']))
            {
                $filedata[] = '<a class="" href="'.$fileinfo->getPathname().'">'.
                              '<img class="img-responsive" src="'.$thumbs_directory.'/'.sha1($fileinfo->getPathname()).'.png">'.
                              '<div class="simplegallery-gallery-poster">'.
                              '    <img src="img/zoom.png">'.
                              '</div>'.
                              '</a>';
            }
        }
    }

    return [$dirdata, $filedata];
}

$data = scanFiles(); // 0 = directories
                     // 1 = files
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>lightGallery: thumbnails</title>
        <meta name="description" content="lightGallery lets you to create animated thumbnails for your gallery with the help of thumbnail plugin." />
        <link href="css/main.css" rel="stylesheet">
        <link href="css/justifiedGallery.min.css" rel="stylesheet">
        <link href="css/lightgallery.css" rel="stylesheet">
    </head>
    <body class="simplegallerys">
        <section class="section highlight pdb0">
            <div class="container-fluid">
                <h2 class="anchor-title mrb35" id="normal-thumb">
                    <a href="#normal-thumb">Galeria de fotos</a>
                    <span class="border"></span>
                </h2>
                <div class="simplegallery-gallery mrb50">
                    <div id="folders" class="list-unstyled">
                        <?php
                            array_map(function($a){ print ($a); }, $data[0]);
                        ?>
                    </div>
                    <div id="thumbnails-without-animation" class="list-unstyled">
                        <?php
                            array_map(function($a){ print ($a); }, $data[1]);
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <script src="js/jquery.min.js"></script>
        <script src="js/prettify.js"></script>
        <script src="js/jquery.justifiedGallery.min.js"></script>
        <script src="js/transition.js"></script>
        <script src="js/collapse.js"></script>
        <script src="js/lightgallery-all.min.js"></script>
        <script src="js/jquery.mousewheel.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function()
            {
                window.prettyPrint && prettyPrint()

                //thumbnails without animation
                var $folder = $('#folders');
                var $thumb = $('#thumbnails-without-animation');

                if ($folder.length) {
                    $folder.justifiedGallery({
                        border: 6
                    });
                }

                if ($thumb.length) {
                    $thumb.justifiedGallery({
                        border: 6
                    }).on('jg.complete', function() {
                        $thumb.lightGallery({
                            thumbnail: true,
                            animateThumb: false,
                            showThumbByDefault: false
                        });
                    });
                };
            });
        </script>
    </body>
</html>
