<?php
class Sanitizador {
    public static function limpiarCadena($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    public static function sanitizarEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    public static function escaparHtml($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}
?>