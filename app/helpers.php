<?php 
if (!function_exists("change_format_number")) {
    /**
     * To change format number from request input
     * @param numeric $phone
     * @return numeric @phone
     */
    function change_format_number($phone)
    {
        if (substr($phone, 0, 2) === '08') {
            $phone = preg_replace('/^0/', '+62', $phone);
        }

        return $phone;
    }
}