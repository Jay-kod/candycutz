<?php
/**
 * Candycutz Unified Master Test Orchestrator
 *
 * Sequentially executes all multi-phase verification test suites from Phase 2 through Phase 9:
 * - Phase 2: Authentication Recovery & Multi-Device Isolation
 * - Phase 3: Routing & API v1 Harmonization
 * - Phase 4: Single Unified Expo Mobile App Convergence
 * - Phase 5: Database & Concurrency Logic Hardening
 * - Phase 6: Vue 3 Website Integration & Zero-Regression
 * - Phase 7: Single-VPS Docker & Nginx Infrastructure
 * - Phase 8A: End-to-End Multi-Role QA Lifecycles
 * - Phase 8B: OWASP Penetration Testing & Security Defense
 * - Phase 9: Production Store Release & Launch Handover
 */

declare(strict_types=1);

$phpBinary = PHP_BINARY;
if (empty($phpBinary) || !file_exists($phpBinary)) {
    $phpBinary = 'c:\\xampp\\php\\php.exe';
}

$suites = [
    [
        'phase' => 'Phase 2',
        'title' => 'Authentication Recovery & Multi-Device Isolation',
        'file' => __DIR__ . '/phase2_auth_test.php',
    ],
    [
        'phase' => 'Phase 3',
        'title' => 'Routing & API v1 Harmonization',
        'file' => __DIR__ . '/phase3_routing_test.php',
    ],
    [
        'phase' => 'Phase 4',
        'title' => 'Unified Mobile App Convergence',
        'file' => __DIR__ . '/phase4_mobile_test.php',
    ],
    [
        'phase' => 'Phase 5',
        'title' => 'Database & Concurrency Hardening',
        'file' => __DIR__ . '/phase5_concurrency_test.php',
    ],
    [
        'phase' => 'Phase 6',
        'title' => 'Vue 3 Website Integration',
        'file' => __DIR__ . '/phase6_website_test.php',
    ],
    [
        'phase' => 'Phase 7',
        'title' => 'Single-VPS Docker Infrastructure',
        'file' => __DIR__ . '/phase7_infrastructure_test.php',
    ],
    [
        'phase' => 'Phase 8A',
        'title' => 'End-to-End Multi-Role QA Lifecycles',
        'file' => __DIR__ . '/phase8_e2e_qa_test.php',
    ],
    [
        'phase' => 'Phase 8B',
        'title' => 'OWASP Penetration & Security Defense',
        'file' => __DIR__ . '/phase8_penetration_test.php',
    ],
    [
        'phase' => 'Phase 9',
        'title' => 'Production Store & Launch Handover',
        'file' => __DIR__ . '/phase9_production_readiness_test.php',
    ],
];

echo "=======================================================================\n";
echo " CANDYCUTZ UNIFIED MASTER TEST MATRIX ORCHESTRATOR\n";
echo " Executing 9 test suites across all platform layers...\n";
echo "=======================================================================\n\n";

$results = [];
$totalStart = microtime(true);

foreach ($suites as $suite) {
    echo "▶ Running {$suite['phase']}: {$suite['title']}...\n";
    $suiteStart = microtime(true);

    $cmd = "\"{$phpBinary}\" \"{$suite['file']}\" 2>&1";
    exec($cmd, $outputLines, $exitCode);

    $duration = round(microtime(true) - $suiteStart, 2);
    $passed = ($exitCode === 0);

    // Count [PASS] tags in output
    $passCount = 0;
    foreach ($outputLines as $line) {
        if (strpos($line, '[PASS]') !== false) {
            $passCount++;
        }
    }

    $results[] = [
        'phase' => $suite['phase'],
        'title' => $suite['title'],
        'passed' => $passed,
        'tests' => $passCount,
        'duration' => $duration,
        'output' => $outputLines,
    ];

    $statusStr = $passed ? "\033[32mPASSED\033[0m" : "\033[31mFAILED\033[0m";
    echo "  └─ Status: {$statusStr} ({$passCount} assertions, {$duration}s)\n\n";

    $outputLines = [];
}

$totalDuration = round(microtime(true) - $totalStart, 2);

echo "=======================================================================\n";
echo " CANDYCUTZ MASTER TEST MATRIX RESULTS\n";
echo "=======================================================================\n";
printf("%-10s | %-42s | %-8s | %-6s | %-6s\n", "Phase", "Suite Title", "Status", "Tests", "Time");
echo "-----------------------------------------------------------------------\n";

$totalTests = 0;
$allPassed = true;

foreach ($results as $res) {
    $totalTests += $res['tests'];
    if (!$res['passed']) {
        $allPassed = false;
    }

    $statusLabel = $res['passed'] ? "PASS" : "FAIL";
    printf("%-10s | %-42s | %-8s | %-6d | %-5.2fs\n",
        $res['phase'],
        substr($res['title'], 0, 42),
        $statusLabel,
        $res['tests'],
        $res['duration']
    );
}

echo "=======================================================================\n";
printf(" TOTAL: %d assertions executed in %.2fs across %d verification suites.\n", $totalTests, $totalDuration, count($suites));

if ($allPassed) {
    echo " OVERALL VERIFICATION RESULT: 100% PASS — ZERO REGRESSIONS DETECTED!\n";
    echo "=======================================================================\n";
    exit(0);
} else {
    echo " OVERALL VERIFICATION RESULT: FAILURES DETECTED IN TEST MATRIX.\n";
    echo "=======================================================================\n";
    exit(1);
}
