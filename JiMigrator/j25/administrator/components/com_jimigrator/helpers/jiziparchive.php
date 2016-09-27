<?php
/**
 * @version     $Id: jiziparchive.php 087 2014-12-16 09:38:00Z Anton Wintergerst $
 * @package     PHP JiZipArchive - Imitates native PHP ZipArchive
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiZipArchive {
    public $numFiles;

    public function __construct() {
        $this->iscreating = false;
        $this->oldOffset = 0;
        $this->datalength = 0;
        $this->controllength = 0;
        $this->centralDirectoryCount = 0;
        $this->endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    }

    /**
     * Function to create the directory where the file(s) will be unzipped
     *
     * @param string $dirname
     * @access public
     * @return void
     */
    public function addEmptyDir($dirname) {
        $dirname = '/'.str_replace("\\", "/", $dirname);
        $feedArrayRow = "\x50\x4b\x03\x04";
        $feedArrayRow .= "\x0a\x00";
        $feedArrayRow .= "\x00\x00";
        $feedArrayRow .= "\x00\x00";
        $feedArrayRow .= "\x00\x00\x00\x00";
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("v", strlen($dirname) );
        $feedArrayRow .= pack("v", 0 );
        $feedArrayRow .= $dirname;
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);

        // Add compressed data
        file_put_contents($this->filename, $feedArrayRow, FILE_APPEND);
        $this->datalength+= strlen($feedArrayRow);
        $newOffset = $this->datalength;

        $addCentralRecord = "\x50\x4b\x01\x02";
        $addCentralRecord .="\x00\x00";
        $addCentralRecord .="\x0a\x00";
        $addCentralRecord .="\x00\x00";
        $addCentralRecord .="\x00\x00";
        $addCentralRecord .="\x00\x00\x00\x00";
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("v", strlen($dirname) );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("V", 16 );
        $addCentralRecord .= pack("V", $this->oldOffset );
        $this->oldOffset = $newOffset;
        $addCentralRecord .= $dirname;

        // Add to cdtmp (appended to main zip on close)
        file_put_contents($this->filename.'.cdtmp', $addCentralRecord, FILE_APPEND);
        $this->controllength+= strlen($addCentralRecord);
        $this->centralDirectoryCount++;
    }

    /**
     * Function to add file to the specified directory in the archive
     *
     * @param string $filename
     * @param string $localname
     * @return void
     * @access public
     */
    public function addFile($filename, $localname) {
        $data = file_get_contents($filename);

        $localname = str_replace("\\", "/", $localname);
        $feedArrayRow = "\x50\x4b\x03\x04";
        $feedArrayRow .= "\x14\x00";
        $feedArrayRow .= "\x00\x00";
        $feedArrayRow .= "\x08\x00";
        $feedArrayRow .= "\x00\x00\x00\x00";
        $uncompressedLength = strlen($data);
        $compression = crc32($data);
        $gzCompressedData = gzcompress($data);
        $gzCompressedData = substr( substr($gzCompressedData, 0, strlen($gzCompressedData) - 4), 2);
        $compressedLength = strlen($gzCompressedData);
        $feedArrayRow .= pack("V",$compression);
        $feedArrayRow .= pack("V",$compressedLength);
        $feedArrayRow .= pack("V",$uncompressedLength);
        $feedArrayRow .= pack("v", strlen($localname) );
        $feedArrayRow .= pack("v", 0 );
        $feedArrayRow .= $localname;
        $feedArrayRow .= $gzCompressedData;
        $feedArrayRow .= pack("V",$compression);
        $feedArrayRow .= pack("V",$compressedLength);
        $feedArrayRow .= pack("V",$uncompressedLength);

        // Add compressed data
        file_put_contents($this->filename, $feedArrayRow, FILE_APPEND);
        $this->datalength+= strlen($feedArrayRow);
        $newOffset = $this->datalength;

        $addCentralRecord = "\x50\x4b\x01\x02";
        $addCentralRecord .="\x00\x00";
        $addCentralRecord .="\x14\x00";
        $addCentralRecord .="\x00\x00";
        $addCentralRecord .="\x08\x00";
        $addCentralRecord .="\x00\x00\x00\x00";
        $addCentralRecord .= pack("V",$compression);
        $addCentralRecord .= pack("V",$compressedLength);
        $addCentralRecord .= pack("V",$uncompressedLength);
        $addCentralRecord .= pack("v", strlen($localname) );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("V", 32 );
        $addCentralRecord .= pack("V", $this->oldOffset );
        $this->oldOffset = $newOffset;
        $addCentralRecord .= $localname;

        // Add to cdtmp (appended to main zip on close)
        file_put_contents($this->filename.'.cdtmp', $addCentralRecord, FILE_APPEND);
        $this->controllength+= strlen($addCentralRecord);
        $this->centralDirectoryCount++;
    }
    public function addFileAndParentsToZip($file, $zipdir = '') {
        if(!empty($zipdir)) $this->addEmptyDir($zipdir);
        $currentdir = $zipdir;
        // Find Parent Directories
        $subdirs = trim(str_replace(JPATH_SITE, '', $file), '/');
        $subdirs = explode('/', $subdirs);
        if(count($subdirs)>0) {
            // Add Parent Directories
            foreach($subdirs as $subdir) {
                if(strpos($subdir.'/', '.')===false) {
                    $currentdir.= $subdir.'/';
                    $this->addEmptyDir($zipdir.$subdir.'/');
                }
            }
        }
        if($currentdir=='/') $currentdir = '';
        // Add File
        $fileparts = explode('/', $file);
        $pathparts = end($pathparts);
        $filename = trim($pathparts, '/');
        if(is_dir($file)) {
            $this->addEmptyDir($currentdir.$filename);
        } elseif(is_file($file)) {
            $this->addFile($file, $currentdir.$filename);
        }
    }
    public function addDirectoryAndFilesToZip($dir, $zipdir = ''){
        if(is_dir($dir)) {
            if($dh = opendir($dir)) {
                //Add the directory
                if(!empty($zipdir)) $this->addEmptyDir($zipdir);
                // Loop through all the files
                while (($file = readdir($dh)) !== false) {
                    //If it's a folder, run the function again!
                    if(!is_file($dir . $file)) {
                        // Skip parent and root directories
                        if( ($file !== ".") && ($file !== "..")) {
                            $this->addDirectoryAndFilesToZip($dir.$file."/", $zipdir.$file."/");
                        }
                    } else {
                        // Add the files
                        $this->addFile($dir.$file, $zipdir.$file);
                    }
                }
            }
        }
    }

    public function open($filename, $create=false) {
        $this->filename = $filename;
        if($create) {
            $this->iscreating = true;
            file_put_contents($filename, '');
            file_put_contents($filename.'.cdtmp', '');
            return true;
        } elseif(file_exists($filename)) {
            $this->numFiles = $this->getTotal($filename, "\x50\x4b\x03\x04");
            return true;
        } else {
            return false;
        }
    }
    public function close() {
        if($this->iscreating) {
            // Load and add central directory
            if(($handle = fopen($this->filename.'.cdtmp', 'r'))!==false) {
                while(($row = fgets($handle, 1000))!==false) {
                    file_put_contents($this->filename, $row, FILE_APPEND);
                }
                fclose($handle);
            }

            // End zip archive
            $data = $this->endOfCentralDirectory.
                pack("v", $this->centralDirectoryCount).
                pack("v", $this->centralDirectoryCount).
                pack("V", $this->controllength).
                pack("V", $this->datalength).
                "\x00\x00";
            file_put_contents($this->filename, $data, FILE_APPEND);
            if(file_exists($this->filename.'.cdtmp')) unlink($this->filename.'.cdtmp');
            $this->iscreating = false;
        }
    }

    /**
     * Get total items in large file
     * @param string $filename
     * @param string $delimiter
     * @return int
     */
    public function getTotal($filename, $delimiter) {
        $chunksize = 1*(1024*1024);
        $total = 0;
        $handle = fopen($filename, 'rb');
        if($handle === false) return 0;

        while(!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            $total+= substr_count($buffer, $delimiter);
        }
        return $total;
    }

    /**
     * @param $index
     * @return mixed Returns filename string or false if not found
     */
    public function getNameIndex($index) {
        $delimiter = "\x50\x4b\x03\x04";
        $chunksize = 1*(1024*1024);
        $i = 0;
        $handle = fopen($this->filename, 'rb');
        if($handle===false) return false;
        $result = false;

        $this->scratch('');
        while(!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            $filespan = substr_count($buffer, $delimiter);
            if($filespan==0) {
                // More data coming for this file
                $this->scratch($buffer, true);
            } else {
                // Has enough for at least one file
                $parts = explode($delimiter, $buffer);
                $hasremainder = (strrpos($buffer, $delimiter)==strlen($buffer)-strlen($delimiter));
                foreach($parts as $x=>$part) {
                    // Append to scratch
                    $this->scratch($part, true);
                    if($hasremainder && $x==count($parts)-1) {

                    } else {
                        // Read file
                        if($i==$index) {
                            $wholedata = file_get_contents($this->filename.'.scratch');
                            $file = $this->decode($wholedata, ($i==0));
                            if($file && isset($file['name'])) {
                                $result = $file['name'];
                                break;
                            }
                        }

                        // Reset scratch
                        $this->scratch('');
                        $i++;
                    }
                }
            }
        }
        fclose($handle);
        return $result;
    }

    /**
     * @param string $destination
     * @param mixed $entries
     * @return bool
     */
    public function extractTo($destination, $entries = null) {
        if($entries==null) {
            $entries = array();
        } elseif(!is_array($entries)) {
            $entries = array($entries);
        }
        $delimiter = "\x50\x4b\x03\x04";
        $chunksize = 1*(1024*1024);
        $i = 0;
        $handle = fopen($this->filename, 'rb');
        if($handle===false) return false;
        $result = false;

        $this->scratch('');
        while(!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            $filespan = substr_count($buffer, $delimiter);
            if($filespan==0) {
                // More data coming for this file
                $this->scratch($buffer, true);
            } else {
                // Has enough for at least one file
                $parts = explode($delimiter, $buffer);
                $hasremainder = (strrpos($buffer, $delimiter)==strlen($buffer)-strlen($delimiter));
                foreach($parts as $x=>$part) {
                    // Append to scratch
                    $this->scratch($part, true);
                    if($hasremainder && $x==count($parts)-1) {

                    } else {
                        // Read file
                        $wholedata = file_get_contents($this->filename.'.scratch');
                        $file = $this->decode($wholedata, ($i==0));
                        if($file && isset($file['name'])) {
                            $result = $file['name'];
                            if(in_array($file['name'], $entries)) {
                                $this->saveFile($destination, $file);
                            }
                        }

                        // Reset scratch
                        $this->scratch('');
                        $i++;
                    }
                }
            }
        }
        fclose($handle);
        return $result;
    }

    private function scratch($data, $append=false) {
        if($append) {
            file_put_contents($this->filename.'.scratch', $data, FILE_APPEND);
        } else {
            file_put_contents($this->filename.'.scratch', $data);
        }
    }

    private function decode($filedata, $isfirst=false) {
        if(!isset($isfirst)) {
            $filesecta = explode("\x50\x4b\x05\x06", $filedata);

            // ZIP Comment
            if(isset($filesecta[1])) {
                $unpackeda = unpack('x16/v1length', $filesecta[1]);
                $comment = substr($filesecta[1], 18, $unpackeda['length']);
                $comment = str_replace(array("\r\n", "\r"), "\n", $comment); // CR + LF and CR -> LF
            }

            // Cut entries from the central directory
            $filesecta = explode("\x50\x4b\x01\x02", $filedata);
            $filesecta = explode("\x50\x4b\x03\x04", $filesecta[0]);
            array_shift($filesecta); // Removes empty entry/signature
        } else {
            $filesecta = explode("\x50\x4b\x01\x02", $filedata);
        }
        foreach($filesecta as $filedata)
        {
            // CRC:crc, FD:file date, FT: file time, CM: compression method, GPF: general purpose flag, VN: version needed, CS: compressed size, UCS: uncompressed size, FNL: filename length
            $file = array();
            $file['error'] = "";

            if($filedata!=null) {
                $unpackeda = unpack("v1version/v1general_purpose/v1compress_method/v1file_time/v1file_date/V1crc/V1size_compressed/V1size_uncompressed/v1filename_length", $filedata);

                // Check for encryption
                $isencrypted = (($unpackeda['general_purpose'] & 0x0001) ? true : false);

                // Check for value block after compressed data
                if($unpackeda['general_purpose'] & 0x0008)
                {
                    $unpackeda2 = unpack("V1crc/V1size_compressed/V1size_uncompressed", substr($filedata, -12));

                    $unpackeda['crc'] = $unpackeda2['crc'];
                    $unpackeda['size_compressed'] = $unpackeda2['size_uncompressed'];
                    $unpackeda['size_uncompressed'] = $unpackeda2['size_uncompressed'];

                    unset($unpackeda2);
                }

                $file['name'] = substr($filedata, 26, $unpackeda['filename_length']);

                if(substr($file['name'], -1) != "/") // skip directories
                {

                    //continue;
                    //}

                    $file['dir'] = dirname($file['name']);
                    $file['dir'] = ($file['dir'] == "." ? "" : $file['dir']);
                    $file['name'] = basename($file['name']);


                    $filedata = substr($filedata, 26 + $unpackeda['filename_length']);


                    if(strlen($filedata) != $unpackeda['size_compressed'])
                    {
                        $file['error'] = "Compressed size is not equal to the value given in header.";
                    }

                    if($isencrypted)
                    {
                        $file['error'] = "Encryption is not supported.";
                    }
                    else
                    {
                        switch($unpackeda['compress_method'])
                        {
                            case 0: // Stored
                                // Not compressed, continue
                                break;
                            case 8: // Deflated
                                $filedata = gzinflate($filedata);
                                break;
                            case 12: // BZIP2
                                if(!extension_loaded("bz2"))
                                {
                                    @dl((strtolower(substr(PHP_OS, 0, 3)) == "win") ? "php_bz2.dll" : "bz2.so");
                                }

                                if(extension_loaded("bz2"))
                                {
                                    $filedata = bzdecompress($filedata);
                                }
                                else
                                {
                                    $file['error'] = "Required BZIP2 Extension not available.";
                                }
                                break;
                            default:
                                $file['error'] = "Compression method ({$unpackeda['compress_method']}) not supported.";
                        }

                        if(!$file['error'])
                        {
                            if($filedata === false)
                            {
                                $file['error'] = "Decompression failed.";
                            }
                            elseif(strlen($filedata) != $unpackeda['size_uncompressed'])
                            {
                                $file['error'] = "File size is not equal to the value given in header.";
                            }
                            elseif(crc32($filedata) != $unpackeda['crc'])
                            {
                                $file['error'] = "CRC32 checksum is not equal to the value given in header.";
                            }
                        }

                        $file['filemtime'] = mktime(($unpackeda['file_time']  & 0xf800) >> 11,($unpackeda['file_time']  & 0x07e0) >>  5, ($unpackeda['file_time']  & 0x001f) <<  1, ($unpackeda['file_date']  & 0x01e0) >>  5, ($unpackeda['file_date']  & 0x001f), (($unpackeda['file_date'] & 0xfe00) >>  9) + 1980);
                        $file['data'] = $filedata;
                    }
                }

            }
            return $file;
        }
        return false;
    }

    /**
     * @param $destination
     * @param $file
     * @return bool
     */
    private function saveFile($destination, $file) {
        // Build subdirectory
        if(isset($file['dir']) && $file['dir']!='') {
            $dir = rtrim($file['dir'], '/');
        } else {
            $dir = '';
        }
        if($file['name']!='.') {
            // Trim directory
            $dir = rtrim($destination.'/'.$dir, '/');
            // Create directories if required
            if(!file_exists($dir)) mkdir($dir, 0755, true);
            if(strstr($file['name'], '.')==false) {
                // Create a subdirectory
                if(!file_exists($dir.'/'.$file['name']))  {
                    if(!mkdir($dir.'/'.$file['name'], 0755, true)) {
                        // Something went wrong creating the subdirectory
                        return false;
                    }
                }
                return true;
            } else {
                // Write data to file
                $output = $dir.'/'.$file['name'];
                if(file_put_contents($output, $file['data'])) {
                    return true;
                } else {
                    // Something went wrong writing the file
                    return false;
                }
            }
        }
        return false;
    }
}
?>