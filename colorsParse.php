#!/usr/bin/php

<?php

$url = $argv[1];
$destPath = "";

$headerFileName         = "UIColor+VunityColors.h";
$implementationFileName = "UIColor+VunityColors.m";

$width = shell_exec('tput cols');
if(!$width) {$width = 80;}
$outputDivider = str_repeat('-',$width);

if($argv[2])
{
  $destPath = $argv[2];
  echo("Destination Path:\n".$destPath."\n");
  echo $outputDivider."\n";
}

$colorFileLines = file_get_contents("https://docs.google.com/spreadsheet/pub?key=0AheacBOaqefrdFFkWXA5a25rNVprR2V1SmJyQzhCNGc&single=true&gid=0&output=txt");
// var_dump($colorFileLines);

  echo("Destination Path:\n\tHeader: ".$headerFileName."\n\tImplementation: ".$implementationFileName."\n");
  echo $outputDivider."\n";

$colorFileLines = explode("\n", $colorFileLines);

$iOSFiles = array();

if (count($colorFileLines) > 0)
{
  $fheader = fopen($destPath.$headerFileName, "w");
  $fimp = fopen($destPath.$implementationFileName, "w");
  
  writeHeaders($fheader, $fimp);
  
  $i = 0;
  foreach ($colorFileLines as $line)
  {
    if (trim($line) == "") // Get rid of empty lines
    {
      continue;
    }

    $fields = explode("\t", $line);

    $colorKey = $fields[0];
    $colorValue = $fields[1];
    $colorAlpha = $fields[3];

    if($colorKey == "ColorKey")
    {
      continue;
    }
    
    writeHeaderColor($fheader, $colorKey);
    writeImplementationColor($fimp, $colorKey, $colorValue, $colorAlpha);

    echo("ColorKey: ".$colorKey."\nColorValue: ".$colorValue."\n");
    echo $outputDivider."\n";

    $i++;
  }
  writeFinals($fheader, $fimp);

  fclose($fheader);
  fclose($fimp);
}
else
{
  die("Error opening file $localizationFileName");
}

function writeLineInFile($file, $line)
{
    fwrite($file, $line."\n");
}

function writeHeaderColor($file, $colorKey)
{
    writeLineInFile($file, "+ (UIColor *)".$colorKey.";\n");
}

function writeImplementationColor($file, $colorKey, $colorValue, $colorAlpha)
{
    $rgb = explode(",", $colorValue);
    writeLineInFile($file, "+ (UIColor *)".$colorKey);
    writeLineInFile($file, "{");
    writeLineInFile($file, "\treturn [UIColor colorWithRed:".$rgb[0].".0/255.0 green:".$rgb[1].".0/255.0 blue:".$rgb[2].".0/255 alpha:".$colorAlpha.".0/100.0];");
    writeLineInFile($file, "}\n");
}

function writeHeaders($headerFile, $implementationFile)
{
    writeLineInFile($headerFile, "//\n//  UIColor+VunityColors.h\n//  Created by Oriol Blanc on 20/06/13.\n//\n");
    writeLineInFile($headerFile, "@interface UIColor (VunityColors)\n");

    writeLineInFile($implementationFile, "//\n//  UIColor+VunityColors.m\n//  Created by Oriol Blanc on 20/06/13.\n//\n");
    writeLineInFile($implementationFile, "#import \"UIColor+VunityColors.h\"\n");
    writeLineInFile($implementationFile, "@implementation UIColor (VunityColors)\n");
}

function writeFinals($headerFile, $implementationFile)
{
    writeLineInFile($headerFile, "@end\n");

    writeLineInFile($implementationFile, "@end\n");
}

function createPathIfDoesntExists($filename)
{
    $dirname = dirname($filename);
     if (!is_dir($dirname))
    {
        mkdir($dirname, 0755, true);
    }
}
