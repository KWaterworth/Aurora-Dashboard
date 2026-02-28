<?php
header('Content-Type: application/json');

$cacheDir = __DIR__ . "/cache/";
$cacheFile = $cacheDir . "aurora.json";
$cacheLifetime = 300; // 5 minutes
$sourceUrl = "https://services.swpc.noaa.gov/json/ovation_aurora_latest.json";

// Get user coordinates
$userLat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$userLon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;

// Clamp values
if ($userLat !== null) $userLat = max(min($userLat, 90), -90);
if ($userLon !== null) $userLon = max(min($userLon, 180), -180);

// Ensure cache directory exists
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

function fetchAuroraData($url) {
    $data = file_get_contents($url);
    if ($data !== false && json_decode($data) !== null) return $data;
    return false;
}

// Check cache
$cacheExpired = !file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $cacheLifetime;
if ($cacheExpired) {
    $fp = fopen($cacheFile, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        clearstatcache(true, $cacheFile);
        $cacheExpired = !file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $cacheLifetime;
        if ($cacheExpired) {
            $newData = fetchAuroraData($sourceUrl);
            if ($newData !== false && json_decode($newData) !== null) {
                $tmpFile = $cacheFile . '.tmp';
                file_put_contents($tmpFile, $newData, LOCK_EX);
                rename($tmpFile, $cacheFile);
            }
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

// Read cache

// Check file exists
if (!file_exists($cacheFile)) {
    echo json_encode(['success' => false, 'error' => 'Cache file missing']);
    exit;
}
// Get contents
$cacheContents = file_get_contents($cacheFile);
if ($cacheContents === false) {
    echo json_encode(['success' => false, 'error' => 'Could not read cache file']);
    exit;
}
// Decode it
$data = json_decode($cacheContents, true);
if ($data === null) {
    echo json_encode(['success' => false, 'error' => 'Cache contains invalid JSON']);
    exit;
}
// Validate data
if ($data === null || !isset($data['coordinates'])) {
    echo json_encode(['success' => false, 'error' => 'Failed to load aurora data']);
    exit;
}

// Lead time (minutes)
$obs = strtotime($data['Observation Time']);
$forecast = strtotime($data['Forecast Time']);
$leadTimeMinutes = ($forecast - $obs) / 60 - 4;  // -4 adjustment for server vs data timestamp offset

// Default: nearest intensity & probability
$nearestIntensity = null;
$probability = null;
$category = null;

if ($userLat !== null && $userLon !== null) {
    // Find nearest OVATION point
    $minDist = PHP_FLOAT_MAX;
    foreach ($data['coordinates'] as [$lon, $lat, $intensity]) {
        // Calculate approximate distance
        $dist = sqrt(($lon - $userLon)**2 + ($lat - $userLat)**2);
        if ($dist < $minDist) {
            $minDist = $dist;
            $nearestIntensity = $intensity;
        }
    }

    // Map intensity to probability
    $probability = min(max(round($nearestIntensity / 9 * 100), 0), 100);

    // Assign category based on intensity
    $categories = [
        [0, 'None'],
        [2, 'Very Weak'],
        [4, 'Weak'],
        [6, 'Moderate'],
        [8, 'Strong'],
        [9, 'Very Strong']
    ];
    
    foreach ($categories as [$max, $label]) {
        if ($nearestIntensity <= $max) {
            $category = $label;
            break;
        }
    }
}

// Return everything
echo json_encode([
    'success' => true,
    'latitude' => $userLat,
    'longitude' => $userLon,
    'observation_time' => $data['Observation Time'] ?? null,
    'forecast_time' => $data['Forecast Time'] ?? null,
    'lead_time_minutes' => $leadTimeMinutes,
    'intensity' => $nearestIntensity,
    'probability' => $probability,
    'category' => $category
]);