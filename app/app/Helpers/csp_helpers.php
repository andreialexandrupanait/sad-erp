<?php

if (!function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for the current request.
     *
     * Use this in blade templates for inline scripts and styles:
     * <script nonce="{{ csp_nonce() }}">...</script>
     * <style nonce="{{ csp_nonce() }}">...</style>
     *
     * @return string
     */
    function csp_nonce(): string
    {
        return request()->attributes->get('csp_nonce', '');
    }
}
