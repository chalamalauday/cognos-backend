<?php
// COGNOS 2K26 - Root Entry Router
// Redirects to cognos or frontend folder
if (is_dir(__DIR__ . '/cognos')) {
    header("Location: cognos/");
} else {
    header("Location: frontend/");
}
exit;
