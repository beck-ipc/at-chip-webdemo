<?php

$adcPath         = '/sys/bus/iio/devices/iio:device1';
$proximityFile   = $adcPath . '/in_proximity_raw';
$ambientFile     = $adcPath . '/in_illuminance_raw';

$temperaturePath = '/sys/bus/iio/devices/iio:device0';
$temperatureFile = $temperaturePath . '/in_temp_input';

$gpioPath        = '/sys/class/gpio';
$led_gpio_base   = 496;

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

function setLedBar($percent) {
  global $gpioPath;
  global $led_gpio_base;

  for ($i = 0; $i < 8; $i++) {
    $led_gpio = $led_gpio_base + $i;
    if ($percent > ($i + 1) * 100.0/8) {
      file_put_contents($gpioPath . '/gpio' . $led_gpio . '/value', '1');
    } else {
      file_put_contents($gpioPath . '/gpio' . $led_gpio . '/value', '0');
    }
    
  }
}


// Set content-type to application/json
header('Content-Type: application/json');

$ambient = getAmbient();
$ambient_percent = getAmbientPercent();

$proximity = getProximity();
$proximity_percent = getProximityPercent();

$temperature = getTemperature();

echo('{"result":0, "ambient":' . $ambient . ', "ambientScaledPerCent":' . number_format($ambient_percent, 1) . ', "proximity":' . $proximity . ', "proximityScaledPerCent":' . number_format($proximity_percent, 1) . ', "temperature":' . number_format($temperature, 1) . '}');

setLedBar($proximity_percent);

?>
