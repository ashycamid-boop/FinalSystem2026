<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php'; // provides $pdo

$out = [
    'total_users' => 0,
    'spot_reports' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0],
    'cases' => [
        'total' => 0,
        'statuses' => [
            'Under Investigation' => 0,
            'Pending Review' => 0,
            'For Filing' => 0,
            'Filed in Court' => 0,
            'Ongoing Trial' => 0,
            'Resolved' => 0,
            'Dismissed' => 0,
            'Archived' => 0,
            'On Hold' => 0,
            'Under Appeal' => 0
        ]
    ],
    'equipment' => ['total' => 0, 'assigned' => 0, 'available' => 0, 'under_maintenance' => 0, 'damaged' => 0, 'out_of_service' => 0],
    'service_requests' => ['total' => 0, 'pending' => 0, 'ongoing' => 0, 'completed' => 0],
    'apprehended' => ['persons' => 0, 'vehicles' => 0, 'items' => 0]
    , 'user_roles' => [], 'user_roles_raw' => []
];

try {
    // total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $out['total_users'] = (int)$stmt->fetchColumn();

    // spot reports counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM spot_reports");
    $out['spot_reports']['total'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT LOWER(TRIM(status)) AS s, COUNT(*) AS c FROM spot_reports GROUP BY LOWER(TRIM(status))");
    $stmt->execute();
    while ($r = $stmt->fetch()) {
        $s = $r['s']; $c = (int)$r['c'];
        if (strpos($s, 'approved') !== false) $out['spot_reports']['approved'] += $c;
        elseif (strpos($s, 'pending') !== false) $out['spot_reports']['pending'] += $c;
        elseif (strpos($s, 'reject') !== false || strpos($s, 'denied') !== false) $out['spot_reports']['rejected'] += $c;
    }

    // cases: consider spot_reports with status = approved as cases, group by case_status
    $stmt = $pdo->prepare("SELECT LOWER(TRIM(case_status)) AS cs, COUNT(*) AS c FROM spot_reports WHERE LOWER(TRIM(status)) = 'approved' GROUP BY LOWER(TRIM(case_status))");
    $stmt->execute();
    $totalCases = 0;
    while ($r = $stmt->fetch()) {
        $s = trim((string)$r['cs']);
        $c = (int)$r['c'];
        $totalCases += $c;
        $norm = strtolower($s);
        if ($norm === '' || strpos($norm,'invest') !== false) $out['cases']['statuses']['Under Investigation'] += $c;
        elseif (strpos($norm,'pending') !== false || strpos($norm,'review') !== false) $out['cases']['statuses']['Pending Review'] += $c;
        elseif (strpos($norm,'for filing') !== false || strpos($norm,'for-filing') !== false) $out['cases']['statuses']['For Filing'] += $c;
        elseif (strpos($norm,'filed') !== false) $out['cases']['statuses']['Filed in Court'] += $c;
        elseif (strpos($norm,'ongoing') !== false || strpos($norm,'trial') !== false) $out['cases']['statuses']['Ongoing Trial'] += $c;
        elseif (strpos($norm,'resolv') !== false) $out['cases']['statuses']['Resolved'] += $c;
        elseif (strpos($norm,'dismiss') !== false) $out['cases']['statuses']['Dismissed'] += $c;
        elseif (strpos($norm,'archiv') !== false) $out['cases']['statuses']['Archived'] += $c;
        elseif (strpos($norm,'hold') !== false) $out['cases']['statuses']['On Hold'] += $c;
        elseif (strpos($norm,'appeal') !== false) $out['cases']['statuses']['Under Appeal'] += $c;
        else $out['cases']['statuses']['Under Investigation'] += $c;
    }
    $out['cases']['total'] = $totalCases;

    // equipment counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM equipment");
    $out['equipment']['total'] = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT LOWER(TRIM(status)) AS s, COUNT(*) AS c FROM equipment GROUP BY LOWER(TRIM(status))");
    $stmt->execute();
    while ($r = $stmt->fetch()) {
        $s = $r['s']; $c = (int)$r['c'];
        if (strpos($s,'assign') !== false || strpos($s,'in use') !== false || strpos($s,'assigned') !== false) $out['equipment']['assigned'] += $c;
        elseif (strpos($s,'avail') !== false) $out['equipment']['available'] += $c;
        elseif (strpos($s,'maint') !== false || strpos($s,'maintenance') !== false) $out['equipment']['under_maintenance'] += $c;
        elseif (strpos($s,'damag') !== false) $out['equipment']['damaged'] += $c;
        elseif ((strpos($s,'out of') !== false && strpos($s,'service') !== false) || strpos($s,'outofservice') !== false || strpos($s,'out_of_service') !== false) $out['equipment']['out_of_service'] += $c;
        else {
            // fallback: if unclear, treat as assigned
            $out['equipment']['assigned'] += $c;
        }
    }

    // service requests
    $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests");
    $out['service_requests']['total'] = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT LOWER(TRIM(status)) AS s, COUNT(*) AS c FROM service_requests GROUP BY LOWER(TRIM(status))");
    $stmt->execute();
    while ($r = $stmt->fetch()) {
        $s = $r['s']; $c = (int)$r['c'];
        if (strpos($s,'pending') !== false || strpos($s,'open') !== false) $out['service_requests']['pending'] += $c;
        elseif (strpos($s,'ongoing') !== false || strpos($s,'scheduled') !== false) $out['service_requests']['ongoing'] += $c;
        elseif (strpos($s,'complete') !== false || strpos($s,'done') !== false) $out['service_requests']['completed'] += $c;
    }

    // apprehended counts (persons, vehicles, items)
    $stmt = $pdo->query("SELECT COUNT(*) FROM spot_report_persons");
    $out['apprehended']['persons'] = (int)$stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM spot_report_vehicles");
    $out['apprehended']['vehicles'] = (int)$stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM spot_report_items");
    $out['apprehended']['items'] = (int)$stmt->fetchColumn();

    // user roles breakdown (raw)
    try {
        $stmt = $pdo->prepare("SELECT TRIM(role) AS role, COUNT(*) AS c FROM users GROUP BY TRIM(role)");
        $stmt->execute();
        $raw = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = $r['role'] ?? '';
            $cnt = (int)$r['c'];
            $raw[$role] = $cnt;
        }
        $out['user_roles_raw'] = $raw;

        // Map common roles to display buckets
        $buckets = ['Enforcement'=>0,'Enforcer'=>0,'Property Custodian'=>0,'Office Staff'=>0,'Admin'=>0,'Other'=>0];
        foreach ($raw as $role => $cnt) {
            $norm = strtolower((string)$role);
            if (strpos($norm,'admin') !== false) $buckets['Admin'] += $cnt;
            elseif (strpos($norm,'custodian') !== false) $buckets['Property Custodian'] += $cnt;
            elseif (strpos($norm,'enforcer') !== false && strpos($norm,'officer') === false) $buckets['Enforcer'] += $cnt;
            elseif (strpos($norm,'enforcement') !== false || strpos($norm,'officer') !== false) $buckets['Enforcement'] += $cnt;
            elseif (strpos($norm,'office') !== false || strpos($norm,'staff') !== false) $buckets['Office Staff'] += $cnt;
            else $buckets['Other'] += $cnt;
        }
        $out['user_roles'] = $buckets;
    } catch (Exception $e) {
        // ignore role breakdown errors
    }

} catch (Exception $e) {
    error_log('dashboard_counts error: ' . $e->getMessage());
}

echo json_encode($out);
exit;

?>
