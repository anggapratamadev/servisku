<?php
// Inline SVG icons for sidebar navigation (stroke style, inherits currentColor).
function nav_icon(string $name): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
        'car'       => '<path d="M5 17h14M3 13l2-6a2 2 0 0 1 1.9-1.4h10.2A2 2 0 0 1 19 7l2 6v4a1 1 0 0 1-1 1h-1.5a1 1 0 0 1-1-1v-1h-11v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><circle cx="7.5" cy="14" r=".8"/><circle cx="16.5" cy="14" r=".8"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'history'   => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        'bell'      => '<path d="M6 9a6 6 0 0 1 12 0c0 6 2 7 2 7H4s2-1 2-7"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.6 3-6 7-6s7 2.4 7 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M22 21c0-2.8-1.7-4.8-4-5.6"/>',
        'wrench'    => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.1L3 17.7 6.3 21l6.3-6.3a4 4 0 0 0 5.1-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>',
        'tool'      => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/><circle cx="12" cy="12" r="3"/>',
        'box'       => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'wallet'    => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/>',
        'chart'     => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'log'       => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.5" cy="6" r="1"/><circle cx="3.5" cy="12" r="1"/><circle cx="3.5" cy="18" r="1"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    ];
    $p = $paths[$name] ?? $paths['dashboard'];
    return '<svg class="nav-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

// Renders the shared shell (sidebar + topbar) for every workspace.
function render_shell(array $cfg, string $current, string $pageTitle, $pdo = null): void
{
    $nc = (function_exists('notification_unread_count') && $pdo) ? notification_unread_count($pdo) : 0;
    $name = (string)($_SESSION['user_name'] ?? $cfg['fallback']);
    $initial = strtoupper(function_exists('mb_substr') ? mb_substr($name, 0, 1) : substr($name, 0, 1));
    echo '<aside class="sidebar" id="sidebar"><div class="brand"><span class="brand-mark">SK</span><div><strong>ServisKu</strong><span>' . htmlspecialchars($cfg['subtitle']) . '</span></div></div><nav aria-label="Menu utama">';
    foreach ($cfg['nav'] as [$file, $label, $icon]) {
        $active = $current === $file ? ' active' : '';
        $badge = ($file === 'notifications.php' && $nc) ? ' <span class="nav-notification-badge">' . htmlspecialchars((string)$nc) . '</span>' : '';
        echo '<a class="' . trim($active) . '" href="' . $file . '"' . ($active ? ' aria-current="page"' : '') . '>' . nav_icon($icon) . '<span>' . $label . '</span>' . $badge . '</a>';
    }
    echo '</nav><a class="logout-link" href="../auth/logout.php">' . nav_icon('logout') . '<span>Keluar</span></a></aside>';
    echo '<main class="main-content"><div class="topbar"><div class="topbar-left"><button type="button" class="icon-button mobile-toggle" data-sidebar-toggle aria-label="Buka menu" aria-controls="sidebar">' . nav_icon('menu') . '</button><div><span class="muted">' . htmlspecialchars($cfg['crumb']) . '</span><h1>' . htmlspecialchars($pageTitle) . '</h1></div></div><div class="user-chip"><span class="avatar">' . htmlspecialchars($initial) . '</span><span class="user-name">' . htmlspecialchars($name) . '</span></div></div>';
}
