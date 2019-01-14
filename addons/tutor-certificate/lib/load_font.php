<?php
// 1. [Required] Point to the composer or dompdf autoloader
require_once "vendor/autoload.php";

// 2. [Optional] Set the path to your font directory
//    By default dopmdf loads fonts to dompdf/lib/fonts
//    If you have modified your font directory set this
//    variable appropriately.
//$fontDir = "lib/fonts";


// *** DO NOT MODIFY BELOW THIS POINT ***

use Dompdf\Dompdf;
use Dompdf\CanvasFactory;
use Dompdf\Exception;
use Dompdf\FontMetrics;
use Dompdf\Options;

use FontLib\Font;

/**
 * Display command line usage
 */
function usage() {
  echo <<<EOD

Usage: {$_SERVER["argv"][0]} font_family [n_file [b_file] [i_file] [bi_file]]

font_family:      the name of the font, e.g. Verdana, 'Times New Roman',
                  monospace, sans-serif. If it equals to "system_fonts", 
                  all the system fonts will be installed.

n_file:           the .ttf or .otf file for the normal, non-bold, non-italic
                  face of the font.

{b|i|bi}_file:    the files for each of the respective (bold, italic,
                  bold-italic) faces.

If the optional b|i|bi files are not specified, load_font.php will search
the directory containing normal font file (n_file) for additional files that
it thinks might be the correct ones (e.g. that end in _Bold or b or B).  If
it finds the files they will also be processed.  All files will be
automatically copied to the DOMPDF font directory, and afm files will be
generated using php-font-lib (https://github.com/PhenX/php-font-lib).

Examples:

./load_font.php silkscreen /usr/share/fonts/truetype/slkscr.ttf
./load_font.php 'Times New Roman' /mnt/c_drive/WINDOWS/Fonts/times.ttf

EOD;
exit;
}

if ( $_SERVER["argc"] < 3 && @$_SERVER["argv"][1] != "system_fonts" ) {
  usage();
}

$dompdf = new DOMPDF();
if (isset($fontDir) && realpath($fontDir) !== false) {
  $dompdf->getOptions()->set('fontDir', $fontDir);
}

/**
 * Installs a new font family
 * This function maps a font-family name to a font.  It tries to locate the
 * bold, italic, and bold italic versions of the font as well.  Once the
 * files are located, ttf versions of the font are copied to the fonts
 * directory.  Changes to the font lookup table are saved to the cache.
 *
 * @param string $fontname    the font-family name
 * @param string $normal      the filename of the normal face font subtype
 * @param string $bold        the filename of the bold face font subtype
 * @param string $italic      the filename of the italic face font subtype
 * @param string $bold_italic the filename of the bold italic face font subtype
 *
 * @throws Exception
 */
function install_font_family($dompdf, $fontname, $normal, $bold = null, $italic = null, $bold_italic = null) {
  $fontMetrics = $dompdf->getFontMetrics();
  
  // Check if the base filename is readable
  if ( !is_readable($normal) )
    throw new Exception("Unable to read '$normal'.");

  $dir = dirname($normal);
  $basename = basename($normal);
  $last_dot = strrpos($basename, '.');
  if ($last_dot !== false) {
    $file = substr($basename, 0, $last_dot);
    $ext = strtolower(substr($basename, $last_dot));
  } else {
    $file = $basename;
    $ext = '';
  }

  if ( !in_array($ext, array(".ttf", ".otf")) ) {
    throw new Exception("Unable to process fonts of type '$ext'.");
  }

  // Try $file_Bold.$ext etc.
  $path = "$dir/$file";
  
  $patterns = array(
    "bold"        => array("_Bold", "b", "B", "bd", "BD"),
    "italic"      => array("_Italic", "i", "I"),
    "bold_italic" => array("_Bold_Italic", "bi", "BI", "ib", "IB"),
  );
  
  foreach ($patterns as $type => $_patterns) {
    if ( !isset($$type) || !is_readable($$type) ) {
      foreach($_patterns as $_pattern) {
        if ( is_readable("$path$_pattern$ext") ) {
          $$type = "$path$_pattern$ext";
          break;
        }
      }
      
      if ( is_null($$type) )
        echo ("Unable to find $type face file.\n");
    }
  }

  $fonts = compact("normal", "bold", "italic", "bold_italic");
  $entry = array();

  // Copy the files to the font directory.
  foreach ($fonts as $var => $src) {
    if ( is_null($src) ) {
      $entry[$var] = $dompdf->getOptions()->get('fontDir') . '/' . mb_substr(basename($normal), 0, -4);
      continue;
    }

    // Verify that the fonts exist and are readable
    if ( !is_readable($src) )
      throw new Exception("Requested font '$src' is not readable");

    $dest = $dompdf->getOptions()->get('fontDir') . '/' . basename($src);

    if ( !is_writeable(dirname($dest)) )
      throw new Exception("Unable to write to destination '$dest'.");

    echo "Copying $src to $dest...\n";

    if ( !copy($src, $dest) )
      throw new Exception("Unable to copy '$src' to '$dest'");
    
    $entry_name = mb_substr($dest, 0, -4);
    
    echo "Generating Adobe Font Metrics for $entry_name...\n";
    
    $font_obj = Font::load($dest);
    $font_obj->saveAdobeFontMetrics("$entry_name.ufm");
    $font_obj->close();

    $entry[$var] = $entry_name;
  }

  // Store the fonts in the lookup table
  $fontMetrics->setFontFamily($fontname, $entry);

  // Save the changes
  $fontMetrics->saveFontFamilies();
}

// If installing system fonts (may take a long time)
if ( $_SERVER["argv"][1] === "system_fonts" ) {
  $fontMetrics = $dompdf->getFontMetrics();
  $files = glob("/usr/share/fonts/truetype/*.ttf") +
    glob("/usr/share/fonts/truetype/*/*.ttf") +
    glob("/usr/share/fonts/truetype/*/*/*.ttf") +
    glob("C:\\Windows\\fonts\\*.ttf") +
    glob("C:\\WinNT\\fonts\\*.ttf") +
    glob("/mnt/c_drive/WINDOWS/Fonts/");
  $fonts = array();
  foreach ($files as $file) {
      $font = Font::load($file);
      $records = $font->getData("name", "records");
      $type = $fontMetrics->getType($records[2]);
      $fonts[mb_strtolower($records[1])][$type] = $file;
      $font->close();
  }
  
  foreach ( $fonts as $family => $files ) {
    echo " >> Installing '$family'... \n";
    
    if ( !isset($files["normal"]) ) {
      echo "No 'normal' style font file\n";
    }
    else {
      install_font_family($dompdf, $family, @$files["normal"], @$files["bold"], @$files["italic"], @$files["bold_italic"]);
      echo "Done !\n";
    }
    
    echo "\n";
  }
}
else {
  call_user_func_array("install_font_family", array_merge( array($dompdf), array_slice($_SERVER["argv"], 1) ));
}
