<?php
$files = glob('/path/to/php-docs-samples/product/src/*.php');
$filesUnchanged = array();

// Tracks whether $line is past function declaration or not.
$processCompletedForThisSample = false;

// Iterate through all the files
foreach ($files as $file) {
  $reading = fopen($file, 'r');
  $writing = fopen($file . '.tmp', 'w');
  $params = array(); // array(variableName => variableType)
  $replaced = false; // Tracks whether current file contents are replaced or not
  $multiLine = false; // Tracks whether function declaration is multiline or not
  $processCompletedForThisSample = false; // Assigning false initially

  while (!feof($reading)) {
    $line = fgets($reading);
    handle_params($line);
    handle_function($line, $replaced, $multiLine);
    fputs($writing, $line);
  }

  fclose($reading);
  fclose($writing);
  if ($replaced) {
    // Replace the original file with temp file
    rename($file . '.tmp', $file);
  } else {
    // Delete the temporary file
    unlink($file . '.tmp');
    $filesUnchanged[] = $file;
  }
}

foreach ($filesUnchanged as $file) {
  $matches;
  preg_match('/src\/(\w+)/', $file, $matches);
  echo 'File Unchanged: ' . $matches[1] . '.php' . PHP_EOL;
}

function handle_params(string $line): void
{
  // If changes done, then just skip the iteration and save resources
  global $processCompletedForThisSample;
  if ($processCompletedForThisSample) return;

  global $params;
  $matches = array();
  if (preg_match('/@param/i', $line)) {
    // This line is one of the @params definition
    // capture type of variable
    preg_match('/\s@param\s([\w\[\]]+)\s\$\w+/i', $line, $matches);
    $variableType = $matches[1];
    if (substr($variableType, -1) == ']') $variableType = 'array';

    // capture variable name
    preg_match('/\s@param\s[\w\[\]]+\s\$(\w+)/i', $line, $matches);
    $variableName = $matches[1];

    // Add the key value of name, type to params array
    $params += [$variableName => $variableType];
  }
}

function check_and_add_type(string &$line, string $key, string $value, bool &$replaced): void
{
  $pattern = sprintf('/(\w+)\s\$' . $key . '/i');
  if (!preg_match($pattern, $line, $matches)) {
    // Need to add variable type
    $pattern = sprintf('/\$' . $key . '/i');
    $replacement = sprintf($value . ' $' . $key);
    $replaced = true;
    $line = preg_replace($pattern, $replacement, $line);
  }
}

function check_and_add_return_type(string &$line, bool &$replaced): void
{
  global $processCompletedForThisSample;
  if (!preg_match('/\):\svoid/i', $line)) {
    // Add the void return type;
    $replacement = sprintf('): void');
    $replaced = true;
    $line = preg_replace('/\)/i', $replacement, $line);
  }
  $processCompletedForThisSample = true;
}

function handle_function(string &$line, bool &$replaced, bool &$multiLine): void
{
  // If changes done, then just skip the iteration and save resources
  global $processCompletedForThisSample;
  if ($processCompletedForThisSample) return;

  global $params;
  $matches = array();
  if ($multiLine) {
    foreach ($params as $key => $value) {
      // If current key not present, then continue
      $pattern = sprintf('/' . $key . '/i');
      if (!preg_match($pattern, $line)) continue;

      // Since present, so check and add type if not present.
      check_and_add_type($line, $key, $value, $replaced);

    }

    if (preg_match('/\)/', $line)) {
      // Check and add if return type not present
      check_and_add_return_type($line, $replaced);
    }
  } else if (preg_match('/function\s\w+\(/i', $line, $matches)) {
    // This means this line is the function declaration

    // Check if this is a multiLine declaration
    if (preg_match('/\($/i', $line)) {
      // Since line ends with (, thus a multiLine comment
      $multiLine = true;
      return;
    }

    foreach ($params as $key => $value) {
      check_and_add_type($line, $key, $value, $replaced);
    }

    // Check and add if return type not present
    check_and_add_return_type($line, $replaced);
  }
}
