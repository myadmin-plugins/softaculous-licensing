<?php

declare(strict_types=1);

// Load the standalone ArrayToXML.php first (it has proper \SimpleXMLElement references).
require_once dirname(__DIR__) . '/src/ArrayToXML.php';

// SoftaculousNOC.php contains a duplicate ArrayToXML class and an array2json function
// after the SoftaculousNOC class closing brace. We extract only the SoftaculousNOC class
// portion into a temporary file to avoid "Cannot redeclare class" fatal errors.
if (!class_exists('Detain\\MyAdminSoftaculous\\SoftaculousNOC', false)) {
    $nocFile = dirname(__DIR__) . '/src/SoftaculousNOC.php';
    $source = file_get_contents($nocFile);

    // Find the end of the SoftaculousNOC class (the closing brace before the duplicate
    // ArrayToXML class declaration). The duplicate starts with a comment line followed by
    // "class ArrayToXML".
    $marker = "\n// Converts an Array to XML\nclass ArrayToXML";
    $pos = strpos($source, $marker);
    if ($pos !== false) {
        $truncated = substr($source, 0, $pos);
        $tmpFile = sys_get_temp_dir() . '/SoftaculousNOC_clean_' . md5($nocFile) . '.php';
        file_put_contents($tmpFile, $truncated);
        require_once $tmpFile;
    } else {
        require_once $nocFile;
    }
}

$autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
}
