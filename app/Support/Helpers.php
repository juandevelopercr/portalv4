<?php
if (!function_exists('getIcon')) {
    function getIcon($mime)
    {
        switch ($mime) {
            case 'application/pdf':
                return 'bxs-file-pdf';
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return 'bxs-file-doc';
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                return 'bxs-file-xls';
            case 'image/jpeg':
            case 'image/png':
                return 'bxs-file-image';
            default:
                return 'bx-file';
        }
    }
}
