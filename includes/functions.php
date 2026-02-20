<?php

/**
 * Escapes a string for safe HTML output.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Safely retrieves and trims a POST value.
 */
function post(string $key): string
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}
