<?php
/**
 * Canonical URL for marketing pages (public path prefix /web, no .php).
 * Included from each page <head>. Modal is noindex and not for ranking.
 */
$script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$base = 'https://serralleriasolidaria.cat/web';

if ($script === 'modal.php') {
    echo '<meta name="robots" content="noindex, nofollow">'."\n";
}

if ($script === 'index.php') {
    $canonical = $base.'/';
} else {
    $slug = preg_replace('/\.php$/i', '', $script);
    $canonical = $base.'/'.$slug;
}

echo '<link rel="canonical" href="'.htmlspecialchars($canonical, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'."\n";
