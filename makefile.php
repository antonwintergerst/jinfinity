<?php
$config_ftp = null;
$config_username = 'demo';
$config_password = 'pass';
$config_destroot = '/root';

if(file_exists('config.php')) {
	include('config.php');
}
?>
<head>
    <link rel="stylesheet" href="media/css/bootstrap.min.css">
    <link rel="stylesheet" href="media/css/bootstrap-theme.min.css">
    <script src="media/js/bootstrap.min.js"></script>
</head>
<body>
<?php
ini_set('max_execution_time', 300);
$task = isset($_GET['task'])? $_GET['task'] : 'scan';
$dir = dirname(__FILE__);
$jversions = array('j15', 'j25', 'j3');

switch($task) {
    case 'scan':
        $files = scandir($dir);
        $extensions = array();
        foreach($files as $file) {
            if(in_array($file, array('.', '..', 'Archives', '+To be Archived', 'j15', 'j25', 'j3', 'media')) || !is_dir($file)) continue;
            $extensions[] = $file;
        } ?>
        <ul>
        <?php
        foreach($extensions as $extension) {
            $version = '1.0.0';
            $makefile = $dir.'/'.$extension.'/make.xml';
            if(is_file($makefile)) {
                // set package version
                $src = $dir.'/j3';
                $makexml = simplexml_load_file($makefile);

                if(is_file($src.'/'.$makexml->manifest)) {
                    $manifest = simplexml_load_file($src.'/'.$makexml->manifest);
                    $version = (string)$manifest->version;
                }
            } else {
                // no make file
                continue;
            }
            $versionparts = explode('.', $version);
            $major = $versionparts[0].'.'.$versionparts[1];
            $build = implode('', $versionparts);
            ?>
            <div>
                <h2><?php echo $extension; ?></h2>
                <div class="actions">
                    <a href="makefile.php?ext=<?php echo $extension; ?>&task=source" class="btn btn-primary">Subversion</a>
                    <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&minor=1" class="btn btn-warning">Minor</a>
                    <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&major=1" class="btn btn-danger">Major</a>
                    <a href="makefile.php?ext=<?php echo $extension; ?>&task=make" class="btn btn-success">Archive</a>
                    <a href="makefile.php?ext=<?php echo $extension; ?>&task=ftp" class="btn btn-info">FTP</a>
                    <form action="makefile.php?ext=<?php echo $extension; ?>&task=changelog" method="post">
                        <textarea style="width: 600px; height: 80px;" name="changes">
<?php echo date('Y-m-d'); ?> [version <?php echo $major; ?> build <?php echo $build; ?>] Anton Wintergerst <support@jinfinity.com>
    * component (change) </textarea>
                        <input type="submit" value="Write Log" />
                    </form>
                </div>
            </div>
        <?php } ?>
        </ul>
        <?php
    break;
    case 'source':
        $extension = $_GET['ext'];
        $increment = isset($_GET['repeat'])? false : true;
        $incrementmajor = isset($_GET['major'])? true : false;
        $incrementminor = isset($_GET['minor'])? true : false;
        if(is_file($dir.'/'.$extension.'/makepro.xml')) {

        }
        $makefile = $dir.'/'.$extension.'/make.xml';
        if(is_file($makefile)) {
            // remove old source
            foreach($jversions as $jversion) {
                $dest = $dir.'/'.$extension.'/'.$jversion;
                if(file_exists($dest)) jidelete($dest);
                // create new source dest
                mkdir($dest, 0755, true);
            }

            $copied = array();
            $makexml = simplexml_load_file($makefile);

            if($increment) {
                // update manifest versions
                foreach($jversions as $jversion) {
                    $src = $dir.'/'.$jversion;
                    foreach($makexml->manifests->manifest as $manifest) {
                        $manifestfile = (string)$manifest;
                        if(!file_exists($src.'/'.$manifestfile)) continue;

                        $manifest = simplexml_load_file($src.'/'.$manifestfile);
                        $versionparts = explode('.', (string)$manifest->version);
                        if($incrementmajor) {
                            $subversion = $versionparts[0];
                            $subversion++;
                            $versionparts[0] = $subversion;
                            $versionparts[count($versionparts)-2] = 0;
                            $versionparts[count($versionparts)-1] = 0;
                        } elseif($incrementminor) {
                            $subversion = $versionparts[count($versionparts)-2];
                            $subversion++;
                            $versionparts[count($versionparts)-2] = $subversion;
                            $versionparts[count($versionparts)-1] = 0;
                        } else {
                            $subversion = (int)end($versionparts);
                            $subversion++;
                            $versionparts[count($versionparts)-1] = $subversion;
                        }
                        $manifest->version = implode('.', $versionparts);
                        echo $manifest->name.' v'.$manifest->version; echo '<br>';

                        // set date
                        $manifest->creationDate = date('F Y');
                        $manifest->copyright = 'Copyright (C) '.date('Y').' Jinfinity';

                        // save xml
                        $manifest->saveXML($src.'/'.$manifestfile);
                    }
                }
            }
            foreach($makexml->files->file as $file) {
                $didcopy = false;
                $file = (string) $file;

                // copy files for each jversion
                foreach($jversions as $jversion) {
                    $src = $dir.'/'.$jversion;
                    $dest = $dir.'/'.$extension.'/'.$jversion;
                    //echo $src.'/'.$file; echo '<br>';

                    if(file_exists($src.'/'.$file)) {
                        if(jicopy($src.'/'.$file, $dest.'/'.$file)) $didcopy = true;
                    }
                }
                if($didcopy) $copied[] = $file;
            }
        }
        echo (count($copied)>0)? 'Success!':'Fail'; ?>
        <h2><?php echo $extension; ?></h2>
        <div class="actions">
            <a href="makefile.php" class="btn btn-default">Back</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&repeat=1" class="btn btn-primary">Re-Source</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&minor=1" class="btn btn-warning">Minor</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&major=1" class="btn btn-danger">Major</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=make" class="btn btn-success">Archive</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=ftp" class="btn btn-info">FTP</a>
        </div>
    <?php
    break;
    case 'make':
        $extension = $_GET['ext'];
        $makefile = $dir.'/'.$extension.'/make.xml';
        if(!is_file($makefile)) return;

        // set package version
        $src = $dir.'/j3';
        $makexml = simplexml_load_file($makefile);
        $manifest = simplexml_load_file($src.'/'.$makexml->manifest);
        $version = (string)$manifest->version;
        $haspro = 1;
        $hasfree = 1;
        if(isset($makexml->haspro)) $haspro = (string) $makexml->haspro;
        if(isset($makexml->hasfree)) $hasfree = (string) $makexml->hasfree;

        $destroot = $dir.'/'.$extension.'/tmp';
        $dest = $destroot.'/src';

        // clear tmp
        if(file_exists($destroot)) jidelete($destroot);
        // create dest src
        mkdir($dest, 0755, true);

        // copy installer
        $src = $dir.'/JiInstaller';
        $files = scandir($src);
        foreach($files as $file) {
            if(in_array($file, array('.', '..'))) continue;
            jicopy($src.'/'.$file, $dest.'/'.$file);
        }

        if($extension!='JiFramework') {
            $frameworkxml = simplexml_load_file($dir.'/JiFramework/make.xml');
        }

        // copy files for each jversion
        foreach($jversions as $jversion) {
            // copy extension
            $src = $dir.'/'.$extension.'/'.$jversion;
            $jdest = $dest.'/extensions/'.$jversion;

            // exclude empty directories
            if(file_exists($src) && count(scandir($src))>2) jicopy($src, $jdest);

            if($extension!='JiFramework') {
                // copy framework
                $src = $dir.'/JiFramework/'.$jversion;
                if(file_exists($src)) {
                    jicopy($src, $jdest);
                }

                $src = $dir.'/'.$jversion;
                // framework excludes
                if(isset($frameworkxml->excludes)) {
                    foreach($frameworkxml->excludes->file as $file) {
                        $file = (string) $file;
                        jidelete($jdest.'/'.$file);
                    }
                }
                // framework includes
                if(isset($frameworkxml->includes)) {
                    foreach($frameworkxml->includes->file as $file) {
                        $file = (string) $file;
                        jicopy($src.'/'.$file, $jdest.'/'.$file);
                    }
                }
            }

            $src = $dir.'/'.$jversion;
            // extension excludes
            if(isset($makexml->excludes)) {
                foreach($makexml->excludes->file as $file) {
                    $file = (string) $file;
                    jidelete($jdest.'/'.$file);
                }
            }
            // extension includes
            if(isset($makexml->includes)) {
                foreach($makexml->includes->file as $file) {
                    $file = (string) $file;
                    jicopy($src.'/'.$file, $jdest.'/'.$file);
                }
            }
        }

        // compare and minimise
        jicompare($dest.'/extensions', $dest.'/extensions/all');

        // split into pro and free
        jiprofree($dest, $destroot);

        // add PRO/FREE to extension and framework manifests
        $manifests = array();

        if($extension!='JiFramework') {
            $frameworkxml = simplexml_load_file($dir.'/JiFramework/make.xml');
            foreach($frameworkxml->manifests->manifest as $manifest) {
                $manifests[] = $manifest;
            }
        }

        foreach($makexml->manifests->manifest as $manifest) {
            $manifests[] = $manifest;
        }
        foreach($jversions as $jversion) {
            // set pro manifests
            $src = $destroot.'/pro/extensions/'.$jversion;
            foreach($manifests as $manifest) {
                $manifestfile = (string)$manifest;
                if(!file_exists($src.'/'.$manifestfile)) continue;

                $manifest = simplexml_load_file($src.'/'.$manifestfile);
                $manifest->version = ((string)$manifest->version).'PRO';
                $manifest->saveXML($src.'/'.$manifestfile);
            }
            // set free manifests
            $src = $destroot.'/free/extensions/'.$jversion;
            foreach($manifests as $manifest) {
                $manifestfile = (string)$manifest;
                if(!file_exists($src.'/'.$manifestfile)) continue;

                $manifest = simplexml_load_file($src.'/'.$manifestfile);
                $manifest->version = ((string)$manifest->version).'FREE';
                $manifest->saveXML($src.'/'.$manifestfile);
            }

            // remove pro files from FREE version
            if(isset($makexml->profiles)) {
                foreach($makexml->profiles->profile as $profile) {
                    $profile = (string) $profile;
                    if(file_exists($src.'/'.$profile)) jidelete($src.'/'.$profile);
                }
            }
        }

        // create index.html files
        jiplaceindex($destroot);

        // zip the contents
        $archivedest = $dir.'/Archives/'.$extension.'/'.date('Y');
        if(!file_exists($archivedest)) mkdir($archivedest, 0755, true);
        jizipcontents($dest, $archivedest.'/'.$extension.'-v'.$version.'-RAW.zip');
        if($hasfree==1) jizipcontents($destroot.'/free', $archivedest.'/'.$extension.'-v'.$version.'.zip');
        if($haspro==1) jizipcontents($destroot.'/pro', $archivedest.'/'.$extension.'-v'.$version.'-PRO.zip');
        echo 'zipped';

        // remove tmp directory
        jidelete($destroot);


        $versionparts = explode('.', $version);
        $major = $versionparts[0].'.'.$versionparts[1];
        $build = implode('', $versionparts);
        ?>
        <h2><?php echo $extension; ?></h2>
        <div class="actions">
            <a href="makefile.php" class="btn btn-default">Back</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&repeat=1" class="btn btn-primary">Re-Source</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&minor=1" class="btn btn-warning">Minor</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&major=1" class="btn btn-danger">Major</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=make" class="btn btn-success">Re-Archive</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=ftp" class="btn btn-info">FTP</a>
            <form action="makefile.php?ext=<?php echo $extension; ?>&task=changelog" method="post">
                <textarea style="width: 600px; height: 80px;" name="changes">
<?php echo date('Y-m-d'); ?> [version <?php echo $major; ?> build <?php echo $build; ?>] Anton Wintergerst <support@jinfinity.com>
    * component (change) </textarea>
                <input type="submit" value="Write Log" />
            </form>
        </div>
    <?php
    break;
    case 'changelog':
        $extension = $_GET['ext'];
        $changes = $_POST['changes'];

        $makefile = $dir.'/'.$extension.'/make.xml';
        if(!is_file($makefile)) return;

        // set package version
        $src = $dir.'/j3';
        $makexml = simplexml_load_file($makefile);
        $manifest = simplexml_load_file($src.'/'.$makexml->manifest);
        $version = (string)$manifest->version;

        file_put_contents($dir.'/Archives/'.$extension.'/'.date('Y').'/'.$extension.'-v'.$version.'-changelog.txt', $changes);
        ?>
        <h1>Log Written!</h1>
        <h2><?php echo $extension; ?></h2>
        <div class="actions">
            <a href="makefile.php" class="btn btn-default">Back</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&repeat=1" class="btn btn-primary">Re-Source</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&minor=1" class="btn btn-warning">Minor</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&major=1" class="btn btn-danger">Major</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=make" class="btn btn-success">Re-Archive</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=ftp" class="btn btn-info">FTP</a>
        </div>
    <?php
    break;
    case 'ftp':
    	if($config_ftp==null) {
    		echo 'FTP config not loaded';
    		return;
    	}
    	
        $extension = $_GET['ext'];

        $makefile = $dir.'/'.$extension.'/make.xml';
        if(!is_file($makefile)) return;

        // get package version
        $src = $dir.'/j3';
        $makexml = simplexml_load_file($makefile);
        $manifest = simplexml_load_file($src.'/'.$makexml->manifest);
        $version = (string)$manifest->version;

        $src = $dir.'/Archives/'.$extension.'/'.date('Y');
        $dest = $config_destroot.'/2015/'.$extension;
        $files = array(
            $extension.'-v'.$version.'-changelog.txt',
            $extension.'-v'.$version.'.zip',
            $extension.'-v'.$version.'-PRO.zip'
        );

        // set up basic connection
        $conn_id = ftp_connect($config_ftp);

        // login with username and password
        $login_result = ftp_login($conn_id, $config_username, $config_password);
        if(!$login_result) {
            echo 'login error';
            return;
        }

        // upload files
        foreach($files as $file) {
            if(file_exists($src.'/'.$file)) {
                if (ftp_put($conn_id, $dest.'/'.$file, $src.'/'.$file, FTP_BINARY)) {
                    echo "successfully uploaded $file"; echo '<br>';
                } else {
                    echo "There was a problem while uploading $file"; echo '<br>';
                }
            }
        }

        // close the connection
        ftp_close($conn_id); ?>
        <h2><?php echo $extension; ?></h2>
        <div class="actions">
            <a href="makefile.php" class="btn btn-default">Back</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&repeat=1" class="btn btn-primary">Re-Source</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&minor=1" class="btn btn-warning">Minor</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=source&major=1" class="btn btn-danger">Major</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=make" class="btn btn-success">Re-Archive</a>
            <a href="makefile.php?ext=<?php echo $extension; ?>&task=ftp" class="btn btn-info">Re-FTP</a>
        </div>
        <?php
    break;
}
function jizipcontents2($source, $destination)
{
    if(file_exists($destination)) jidelete($destination);
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}
function jizipcontents($src, $dest)
{
    if(file_exists($dest)) jidelete($dest);

    $zip = new ZipArchive();

    $result = $zip->open($dest, ZipArchive::CREATE);
    if($result!==true) {
        echo 'Error: '.$result;
        return;
    };

    $files = array();
    $sort = array();
    $iterator =  new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src), RecursiveIteratorIterator::SELF_FIRST
    );
    foreach($iterator as $filename=>$file) {
        if(in_array($file->getFileName(), array('.', '..'))) continue;
        $sort[] = (is_dir($file)? '0':'1').$iterator->getDepth().$file->getFileName();
        $files[] = $file->getRealPath();
    }

    array_multisort($sort, $files, SORT_ASC);

    foreach($files as $file) {
        $relfile = substr(str_replace('\\', '/', $file), strlen($src)+1);
        //echo $relfile; echo '<br>';
        if(is_dir($file)) {
            $zip->addEmptyDir($relfile);
        } elseif(is_file($file)) {
            $zip->addFile($file, $relfile);
        }
    }

    $zip->close();
}
function jiprofree($srcroot, $destroot, $subdir=false)
{
    $src = (!$subdir)? $srcroot : $srcroot.'/'.$subdir;

    $commentstyles = array(
        array('start'=>'/*', 'end'=>'*/'),
        array('start'=>'<!--', 'end'=>'-->')
    );
    $proStart = ' >>> PRO >>> ';
    $proEnd = ' <<< PRO <<< ';
    $freeStart = ' >>> FREE >>> ';
    $freeEnd = ' <<< FREE <<< ';

    $files = scandir($src);
    foreach($files as $file) {
        if(in_array($file, array('.', '..'))) continue;

        if(is_dir($src.'/'.$file)) {
            jiprofree($srcroot, $destroot, (!$subdir)? $file : $subdir.'/'.$file);
            continue;
        }

        $srcfile = $src.'/'.$file;
        $destfile = (!$subdir)? $file : $subdir.'/'.$file;

        $contents = file_get_contents($srcfile);

        $prodest = $destroot.'/pro/'.$destfile;
        $freedest = $destroot.'/free/'.$destfile;
        if(!file_exists(dirname($prodest))) mkdir(dirname($prodest), 0755, true);
        if(!file_exists(dirname($freedest))) mkdir(dirname($freedest), 0755, true);

        $procontents = $contents;
        $freecontents = $contents;
        foreach($commentstyles as $cs) {
            $procontents = preg_replace(array('#('.preg_quote($cs['start'].$proStart.$cs['end']).')#si', '#('.preg_quote($cs['start'].$proEnd.$cs['end']).')#si'), '', $procontents);
            $procontents = preg_replace('#('.preg_quote($cs['start'].$freeStart.$cs['end']).')(.*?)('.preg_quote($cs['start'].$freeEnd.$cs['end']).')#si', '', $procontents);

            $freecontents = preg_replace('#('.preg_quote($cs['start'].$proStart.$cs['end']).')(.*?)('.preg_quote($cs['start'].$proEnd.$cs['end']).')#si', '', $freecontents);
            $freecontents = preg_replace(array('#('.preg_quote($cs['start'].$freeStart.$cs['end']).')#si', '#('.preg_quote($cs['start'].$freeEnd.$cs['end']).')#si'), '', $freecontents);
        }

        file_put_contents($destroot.'/pro/'.$destfile, $procontents);
        file_put_contents($destroot.'/free/'.$destfile, $freecontents);
    }
}
function jicompare($srcroot, $destroot, $subdir=false)
{
    $src = (!$subdir)? $srcroot.'/j3' : $srcroot.'/j3/'.$subdir;
    $jversions = array();
    foreach(scandir($srcroot) as $file) {
        if(in_array($file, array('j15', 'j25'))) $jversions[] = $file;
    }
    if(count($jversions)<=1) return false;

    $files = scandir($src);
    foreach($files as $file) {
        if(in_array($file, array('.', '..'))) continue;

        if(is_dir($src.'/'.$file)) {
            jicompare($srcroot, $destroot, (!$subdir)? $file : $subdir.'/'.$file);
            continue;
        }

        $srcfile = $src.'/'.$file;
        $destfile = (!$subdir)? $file : $subdir.'/'.$file;
        $samesame = true;
        foreach($jversions as $jversion) {
            $comparefile = $srcroot.'/'.$jversion.'/'.$destfile;

            if(file_exists($comparefile)) {
                if(sha1_file($srcfile)!=sha1_file($comparefile)) $samesame = false;
            } else {
                $samesame = false;
            }
        }
        if($samesame) {
            // copy to all directory
            jicopy($srcfile, $destroot.'/'.$destfile, false);

            // delete other versions
            foreach($jversions as $jversion) {
                $comparefile = $srcroot.'/'.$jversion.'/'.$destfile;
                if(file_exists($comparefile)) unlink($comparefile);
            }

            // delete src version
            unlink($srcfile);
        }
    }
}
function jiplaceindex($destroot, $subdir=false)
{
    $dest = (!$subdir)? $destroot : $destroot.'/'.$subdir;

    if(!is_dir($dest)) return;

    $files = scandir($dest);
    foreach($files as $file) {
        if(in_array($file, array('.', '..'))) continue;

        if(is_dir($dest.'/'.$file)) {
            jiplaceindex($destroot, (!$subdir)? $file : $subdir.'/'.$file);
            continue;
        }
    }
    // create index.html
    if(!file_exists($dest.'/index.html')) file_put_contents($dest.'/index.html', '<!DOCTYPE html><title></title>');
}
function jicopy($src, $dest, $recursive=true) {
    if(!file_exists($src)) return false;

    if(is_file($src)) {
        if(!file_exists(dirname($dest))) mkdir(dirname($dest), 0755, true);
        copy($src, $dest);
        return;
    }
    if(!is_dir($src)) return false;

    $dir = opendir($src);
    if(!file_exists($dest)) mkdir($dest, 0755, true);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                if(!$recursive) break;
                jicopy($src . '/' . $file,$dest . '/' . $file);
            }
            else {
                copy($src . '/' . $file, $dest . '/' . $file);
            }
        }
    }
    closedir($dir);
    return true;
}
function jidelete($dir) {
    if(!file_exists($dir)) return false;

    if(is_file($dir)) {
        return unlink($dir);
    } elseif(!is_dir($dir)) {
        return false;
    }
    $files = scandir($dir);
    foreach($files as $file) {
        if(in_array($file, array('.', '..'))) continue;
        $file = $dir.'/'.$file;
        if(is_dir($file)) {
            jidelete($file);
        } else {
            unlink($file);
        }
    }
    if(!in_array($dir, array('.', '..'))) rmdir($dir);
}
?>
</body>