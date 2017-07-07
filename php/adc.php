<?php

$adcPath         = '/sys/bus/iio/devices/iio:device1';
$proximityFile   = $adcPath . '/in_proximity_raw';
$ambientFile     = $adcPath . '/in_illuminance_raw';

$temperaturePath = '/sys/bus/iio/devices/iio:device0';
$temperatureFile = $temperaturePath . '/in_temp_input';

$last_proximity  = 0;
$last_ambient    = 0;

function getProximity() {
  global $proximityFile;
  global $last_proximity;
  $last_proximity = file_get_contents($proximityFile);
  return $last_proximity;
}

function getProximityPercent() {
  global $last_proximity;
  // remove "dead zone"
  $proximity = $last_proximity;

  $tmp = $proximity - 2200;
  if ($tmp < 1) {
    $proximity = 1;
  } else {
    $proximity = $tmp;
  }

  // Scale to percent
  $proximity = $proximity * 0.01579;

  // linearize 
  return $proximity;
//  return 17.783 * log($proximity * 2.77);  
}

function getAmbient() {
  global $ambientFile;
  global $last_ambient;  
  $last_ambient = file_get_contents($ambientFile);
  return $last_ambient;
}

function getAmbientPercent() {
  global $last_ambient;
  // Scale to percent
  $tmp = $last_ambient * 0.001526;

  // linearize 
  return $tmp;
//  return 17.783 * log($tmp * 2.77);
}

function getTemperature() {
  global $temperatureFile;
  return file_get_contents($temperatureFile) / 1000.0;
}

?>
