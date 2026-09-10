<?php

declare(strict_types=1);

/**
 * Candycutz — Phase 9 Production Store Release & Launch Readiness Verification
 * 
 * Verifies:
 * 1. Unified Mobile EAS Configuration (eas.json)
 * 2. Mobile App Store Metadata & Permissions (app.json)
 * 3. Mobile Brand Asset Integrity & Dimensions
 * 4. Regulatory & Store Compliance (Account Deletion & Privacy Policy)
 * 5. Production Operations Handbooks & Operator Triage Runbooks
 * 6. Architecture Integrity & AGENTS.md Conformance
 */

$rootDir = dirname(__DIR__, 2);
$mobileDir = $rootDir . '/candycutz-mobile-app';
$webDir = $rootDir . '/barbing-saloon-web';
$docsDir = $rootDir . '/docs';

$passed = 0;
$failed = 0;

function assertCondition(string $name, bool $condition, string $details = ''): void
{
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $name\n";
        $passed++;
    } else {
        echo " [FAIL] $name" . ($details ? " - $details" : "") . "\n";
        $failed++;
    }
}

echo "=======================================================================\n";
echo " CANDYCUTZ PHASE 9: PRODUCTION STORE RELEASE & LAUNCH VERIFICATION\n";
echo "=======================================================================\n\n";

// --- 1. EAS Build Configuration ---
echo "--- 1. Unified EAS Configuration (eas.json) ---\n";
$easPath = $mobileDir . '/eas.json';
assertCondition("1a. eas.json exists in candycutz-mobile-app", file_exists($easPath));

if (file_exists($easPath)) {
    $easJson = json_decode(file_get_contents($easPath), true);
    assertCondition("1b. eas.json is valid JSON", is_array($easJson));
    assertCondition("1c. eas.json enforces CLI version >= 12.0.0", isset($easJson['cli']['version']) && str_contains($easJson['cli']['version'], '12.0.0'));
    assertCondition("1d. Development build profile exists", isset($easJson['build']['development']['developmentClient']));
    assertCondition("1e. Preview build profile exists with APK output", isset($easJson['build']['preview']['android']['buildType']) && $easJson['build']['preview']['android']['buildType'] === 'apk');
    assertCondition("1f. Production build profile targets Android App Bundle (.aab)", isset($easJson['build']['production']['android']['buildType']) && $easJson['build']['production']['android']['buildType'] === 'app-bundle');
    assertCondition("1g. Production build profile enables autoIncrement", isset($easJson['build']['production']['autoIncrement']) && $easJson['build']['production']['autoIncrement'] === true);
    assertCondition("1h. Production build profile targets physical iOS devices", isset($easJson['build']['production']['ios']['simulator']) && $easJson['build']['production']['ios']['simulator'] === false);
    assertCondition("1i. Production submission profile exists for Google Play", isset($easJson['submit']['production']['android']['track']) && $easJson['submit']['production']['android']['track'] === 'production');
    assertCondition("1j. Production submission profile exists for Apple App Store", isset($easJson['submit']['production']['ios']['appleId']));
}

// --- 2. Store Metadata & Permissions (app.json) ---
echo "\n--- 2. Store Metadata & Permissions (app.json) ---\n";
$appJsonPath = $mobileDir . '/app.json';
assertCondition("2a. app.json exists in candycutz-mobile-app", file_exists($appJsonPath));

if (file_exists($appJsonPath)) {
    $appJson = json_decode(file_get_contents($appJsonPath), true);
    $expo = $appJson['expo'] ?? [];
    
    assertCondition("2b. App name is 'CandyCutz'", ($expo['name'] ?? '') === 'CandyCutz');
    assertCondition("2c. App version is '1.0.0'", ($expo['version'] ?? '') === '1.0.0');
    assertCondition("2d. iOS bundleIdentifier is 'com.candycutz.app'", ($expo['ios']['bundleIdentifier'] ?? '') === 'com.candycutz.app');
    assertCondition("2e. iOS buildNumber is specified", !empty($expo['ios']['buildNumber']));
    assertCondition("2f. iOS NSCameraUsageDescription configured for avatar upload", !empty($expo['ios']['infoPlist']['NSCameraUsageDescription']));
    assertCondition("2g. iOS NSPhotoLibraryUsageDescription configured for photo library", !empty($expo['ios']['infoPlist']['NSPhotoLibraryUsageDescription']));
    assertCondition("2h. Android package is 'com.candycutz.app'", ($expo['android']['package'] ?? '') === 'com.candycutz.app');
    assertCondition("2i. Android versionCode >= 1", ($expo['android']['versionCode'] ?? 0) >= 1);
    assertCondition("2j. Android INTERNET permission declared", in_array('INTERNET', $expo['android']['permissions'] ?? []));
    assertCondition("2k. Android ACCESS_NETWORK_STATE permission declared", in_array('ACCESS_NETWORK_STATE', $expo['android']['permissions'] ?? []));
    assertCondition("2l. Luxury obsidian theme background (#0B0B0B) configured", ($expo['splash']['backgroundColor'] ?? '') === '#0B0B0B');
}

// --- 3. Brand Asset Integrity ---
echo "\n--- 3. Brand Asset Integrity ---\n";
$assetsDir = $mobileDir . '/assets';
$assets = ['icon.png', 'adaptive-icon.png', 'splash.png', 'favicon.png'];
foreach ($assets as $asset) {
    $path = $assetsDir . '/' . $asset;
    $size = file_exists($path) ? filesize($path) : 0;
    assertCondition("3. Asset $asset exists and non-empty (" . round($size / 1024) . " KB)", file_exists($path) && $size > 10000);
}

// --- 4. Regulatory & Store Compliance ---
echo "\n--- 4. Regulatory & Store Compliance (Apple & Google) ---\n";
$deletionPagePath = $webDir . '/src/modules/public/pages/AccountDeletionPage.vue';
assertCondition("4a. AccountDeletionPage.vue exists", file_exists($deletionPagePath));

if (file_exists($deletionPagePath)) {
    $deletionContent = file_get_contents($deletionPagePath);
    assertCondition("4b. AccountDeletionPage documents in-app deactivation flow", str_contains($deletionContent, 'Instant In-App Deactivation'));
    assertCondition("4c. AccountDeletionPage includes web request submission form", str_contains($deletionContent, 'Submit Deletion Request Online'));
    assertCondition("4d. AccountDeletionPage outlines statutory financial data retention", str_contains($deletionContent, 'Statutory Data Retention Notice'));
}

$routesPath = $webDir . '/src/modules/public/routes.js';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    assertCondition("4e. Route /account-deletion registered in public routes", str_contains($routesContent, "'/account-deletion'"));
    assertCondition("4f. Route /privacy registered in public routes", str_contains($routesContent, "'/privacy'"));
    assertCondition("4g. Route /terms registered in public routes", str_contains($routesContent, "'/terms'"));
}

$privacyPagePath = $webDir . '/src/modules/public/pages/PrivacyPage.vue';
if (file_exists($privacyPagePath)) {
    $privacyContent = file_get_contents($privacyPagePath);
    assertCondition("4h. Privacy policy includes Section 6 on Account Deactivation & Erasure", str_contains($privacyContent, 'Account Deactivation & Data Erasure'));
    assertCondition("4i. Privacy policy references Apple Guideline 5.1.1(v)", str_contains($privacyContent, 'Apple Guideline 5.1.1(v)'));
}

$distDir = $webDir . '/dist/assets';
$compiledDeletion = glob($distDir . '/AccountDeletionPage-*.js');
assertCondition("4j. Production compiled bundle contains AccountDeletionPage", !empty($compiledDeletion));

// --- 5. Operations & Triage Runbooks ---
echo "\n--- 5. Operations & Triage Runbooks ---\n";
$operatorRunbookPath = $docsDir . '/debugging/OPERATOR_TRIAGE_RUNBOOK.md';
assertCondition("5a. OPERATOR_TRIAGE_RUNBOOK.md exists", file_exists($operatorRunbookPath));

if (file_exists($operatorRunbookPath)) {
    $opContent = file_get_contents($operatorRunbookPath);
    assertCondition("5b. Runbook covers Keffi municipal power & network outage protocol", str_contains($opContent, 'Power Outage or Local Network Disconnection'));
    assertCondition("5c. Runbook covers Walk-In vs Online reservation conflict priority", str_contains($opContent, 'Walk-In vs Online Booking Slot Conflict'));
    assertCondition("5d. Runbook covers payment discrepancy & webhook failure resolution", str_contains($opContent, 'Customer Debited but Appointment Shows "Pending Payment"'));
    assertCondition("5e. Runbook covers emergency barber absence slot blocking", str_contains($opContent, 'Barber Sudden Absence / Emergency Schedule Block'));
    assertCondition("5f. Runbook covers home service dispatch & Keffi zone surcharges", str_contains($opContent, 'Home Service Dispatch Verification'));
    assertCondition("5g. Runbook provides 4-tier contact & escalation protocol", str_contains($opContent, 'Contact & Escalation Protocol'));
}

$prodRunbookPath = $docsDir . '/deployment/PRODUCTION_RELEASE_RUNBOOK.md';
assertCondition("5h. PRODUCTION_RELEASE_RUNBOOK.md exists", file_exists($prodRunbookPath));

if (file_exists($prodRunbookPath)) {
    $prodContent = file_get_contents($prodRunbookPath);
    assertCondition("5i. Handbook details EAS remote build commands for Android & iOS", str_contains($prodContent, 'eas build --platform android --profile production'));
    assertCondition("5j. Handbook provides store review test credentials & physical service exemption note", str_contains($prodContent, 'Store Review Test Credentials'));
    assertCondition("5k. Handbook details zero-downtime VPS deployment via deploy.sh", str_contains($prodContent, 'deploy.sh'));
    assertCondition("5l. Handbook details backup automation & disaster recovery drill", str_contains($prodContent, 'restore_db.sh'));
    assertCondition("5m. Handbook specifies health check audit endpoints", str_contains($prodContent, '/api/health'));
}

$launchPath = $docsDir . '/Launch.md';
if (file_exists($launchPath)) {
    $launchContent = file_get_contents($launchPath);
    assertCondition("5n. Launch.md references single unified mobile application", str_contains($launchContent, 'candycutz-mobile-app'));
    assertCondition("5o. Launch.md references living operator and release runbooks", str_contains($launchContent, 'OPERATOR_TRIAGE_RUNBOOK.md'));
}

// --- 6. Architecture Integrity & AGENTS.md Conformance ---
echo "\n--- 6. Architecture Integrity & AGENTS.md Conformance ---\n";
assertCondition("6a. Exactly ONE unified mobile app project in candycutz-mobile-app", file_exists($mobileDir . '/package.json') && file_exists($mobileDir . '/app.json'));
assertCondition("6b. Zero separate candycutz-barber-app directory in repository root", !is_dir($rootDir . '/candycutz-barber-app'));
assertCondition("6c. Zero separate candycutz-customer-app directory in repository root", !is_dir($rootDir . '/candycutz-customer-app'));
assertCondition("6d. Single authoritative Laravel backend present", file_exists($rootDir . '/barbing-saloon-api/artisan'));
assertCondition("6e. Unified docker-compose.yml orchestrates single-VPS topology", file_exists($rootDir . '/docker-compose.yml'));

echo "\n=======================================================================\n";
if ($failed === 0) {
    echo " ALL $passed PHASE 9 PRODUCTION STORE & LAUNCH READINESS CHECKS PASSED!\n";
    echo " PLATFORM STATUS: READY FOR STORE SUBMISSION & VPS GO-LIVE!\n";
} else {
    echo " FAILED: $failed checks failed out of " . ($passed + $failed) . " assertions.\n";
}
echo "=======================================================================\n";

exit($failed === 0 ? 0 : 1);
