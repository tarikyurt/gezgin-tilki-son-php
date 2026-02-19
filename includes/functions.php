<?php
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currencyCode)
    {
        $symbol = '€'; // Default
        switch ($currencyCode) {
            case 'USD':
                $symbol = '$';
                break;
            case 'GBP':
                $symbol = '£';
                break;
            case 'TRY':
                $symbol = '₺';
                break;
            case 'EUR':
                $symbol = '€';
                break;
        }
        return $symbol . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol($currencyCode)
    {
        switch ($currencyCode) {
            case 'USD':
                return '$';
            case 'GBP':
                return '£';
            case 'TRY':
                return '₺';
            case 'EUR':
                return '€';
            default:
                return '€';
        }
    }
}
?>